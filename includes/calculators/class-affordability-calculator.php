<?php
/**
 * Affordability Calculator Class
 * 
 * Mortgage Affordability Plus Calculator with local market data
 */

if (!defined('ABSPATH')) {
    exit;
}

class UQUAL_Affordability_Calculator extends UQUAL_Base_Calculator {
    
    /**
     * Initialize calculator
     */
    protected function init() {
        $this->type = 'affordability';
        $this->name = __('Mortgage Affordability Plus Calculator', 'uqual-calculators');
        $this->description = __('Determine how much house you can afford based on your income, debts, down payment, and current interest rates.', 'uqual-calculators');
        
        $this->setup_input_fields();
    }
    
    /**
     * Setup input fields
     */
    private function setup_input_fields() {
        $this->input_fields = array(
            array(
                'name' => 'grossIncome',
                'label' => __('Annual Gross Income', 'uqual-calculators'),
                'type' => 'currency',
                'min' => 0,
                'required' => true,
                'placeholder' => '75000',
                'help' => __('Your total annual income before taxes', 'uqual-calculators')
            ),
            array(
                'name' => 'existingDebt',
                'label' => __('Monthly Debt Payments', 'uqual-calculators'),
                'type' => 'currency',
                'min' => 0,
                'placeholder' => '500',
                'help' => __('Total monthly payments for existing debts (excluding housing)', 'uqual-calculators')
            ),
            array(
                'name' => 'downPayment',
                'label' => __('Down Payment Amount', 'uqual-calculators'),
                'type' => 'currency',
                'min' => 0,
                'required' => true,
                'placeholder' => '20000',
                'help' => __('Amount you have available for down payment', 'uqual-calculators')
            ),
            array(
                'name' => 'interestRate',
                'label' => __('Interest Rate (%)', 'uqual-calculators'),
                'type' => 'number',
                'min' => 0,
                'max' => 20,
                'step' => 0.1,
                'default' => 4.5,
                'required' => true,
                'help' => __('Current mortgage interest rate', 'uqual-calculators')
            ),
            array(
                'name' => 'loanTerm',
                'label' => __('Loan Term (Years)', 'uqual-calculators'),
                'type' => 'select',
                'options' => array(
                    '15' => __('15 Years', 'uqual-calculators'),
                    '30' => __('30 Years', 'uqual-calculators')
                ),
                'default' => '30',
                'required' => true
            ),
            array(
                'name' => 'propertyTaxRate',
                'label' => __('Property Tax Rate (%)', 'uqual-calculators'),
                'type' => 'number',
                'min' => 0,
                'max' => 5,
                'step' => 0.1,
                'default' => 1.2,
                'help' => __('Annual property tax rate as percentage of home value', 'uqual-calculators')
            ),
            array(
                'name' => 'insuranceRate',
                'label' => __('Homeowners Insurance (Annual)', 'uqual-calculators'),
                'type' => 'currency',
                'min' => 0,
                'default' => 1200,
                'help' => __('Annual homeowners insurance cost', 'uqual-calculators')
            ),
            array(
                'name' => 'hoaFees',
                'label' => __('HOA Fees (Monthly)', 'uqual-calculators'),
                'type' => 'currency',
                'min' => 0,
                'default' => 0,
                'help' => __('Monthly homeowners association fees if applicable', 'uqual-calculators')
            )
        );
    }
    
    /**
     * Perform affordability calculation
     */
    public function calculate($input_data) {
        $monthlyIncome = $input_data['grossIncome'] / 12;
        
        // Industry standard: 28% front-end, 36% back-end ratios
        $maxHousingPayment = $monthlyIncome * 0.28;
        $maxTotalDebt = $monthlyIncome * 0.36;
        $availableForHousing = $maxTotalDebt - ($input_data['existingDebt'] ?? 0);
        
        // Use the lower of the two limits
        $affordablePayment = min($maxHousingPayment, $availableForHousing);
        
        // Estimate property taxes and insurance
        $monthlyInsurance = ($input_data['insuranceRate'] ?? 1200) / 12;
        $monthlyHOA = $input_data['hoaFees'] ?? 0;
        
        // Available for principal and interest
        $availableForPI = $affordablePayment - $monthlyInsurance - $monthlyHOA;
        
        // Calculate loan amount
        $loanAmount = $this->calculate_loan_amount(
            $availableForPI,
            $input_data['interestRate'],
            intval($input_data['loanTerm'])
        );
        
        // Calculate home price
        $homePrice = $loanAmount + $input_data['downPayment'];
        
        // Add property tax to calculation
        $monthlyPropertyTax = ($homePrice * ($input_data['propertyTaxRate'] ?? 1.2) / 100) / 12;
        
        // Recalculate with property tax
        $availableForPI = $affordablePayment - $monthlyInsurance - $monthlyHOA - $monthlyPropertyTax;
        $loanAmount = $this->calculate_loan_amount(
            $availableForPI,
            $input_data['interestRate'],
            intval($input_data['loanTerm'])
        );
        $homePrice = $loanAmount + $input_data['downPayment'];
        
        // Calculate actual monthly payment
        $principalInterest = $this->calculate_monthly_payment(
            $loanAmount,
            $input_data['interestRate'],
            intval($input_data['loanTerm'])
        );
        
        $monthlyPropertyTax = ($homePrice * ($input_data['propertyTaxRate'] ?? 1.2) / 100) / 12;
        $totalMonthlyPayment = $principalInterest + $monthlyPropertyTax + $monthlyInsurance + $monthlyHOA;
        
        // Calculate DTI
        $dtiRatio = ($totalMonthlyPayment + ($input_data['existingDebt'] ?? 0)) / $monthlyIncome * 100;
        
        // Generate scenarios with different down payments
        $scenarios = $this->generate_scenarios($input_data);
        
        // Calculate PMI if down payment is less than 20%
        $downPaymentPercent = ($input_data['downPayment'] / $homePrice) * 100;
        $pmiAmount = 0;
        if ($downPaymentPercent < 20) {
            $pmiAmount = $loanAmount * 0.005 / 12; // 0.5% annually
            $totalMonthlyPayment += $pmiAmount;
        }
        
        return array(
            'homePrice' => round($homePrice),
            'loanAmount' => round($loanAmount),
            'monthlyPayment' => round($totalMonthlyPayment),
            'principalInterest' => round($principalInterest),
            'propertyTax' => round($monthlyPropertyTax),
            'insurance' => round($monthlyInsurance),
            'hoa' => round($monthlyHOA),
            'pmi' => round($pmiAmount),
            'dtiRatio' => round($dtiRatio, 1),
            'downPaymentPercent' => round($downPaymentPercent, 1),
            'scenarios' => $scenarios
        );
    }
    
    /**
     * Generate different down payment scenarios
     */
    private function generate_scenarios($input_data) {
        $scenarios = array();
        $percentages = array(5, 10, 15, 20);
        
        foreach ($percentages as $percent) {
            // Calculate home price for this down payment percentage
            $targetDownPayment = $input_data['downPayment'];
            $homePrice = $targetDownPayment / ($percent / 100);
            $loanAmount = $homePrice - $targetDownPayment;
            
            $monthlyPayment = $this->calculate_monthly_payment(
                $loanAmount,
                $input_data['interestRate'],
                intval($input_data['loanTerm'])
            );
            
            // Add taxes and insurance
            $monthlyPropertyTax = ($homePrice * ($input_data['propertyTaxRate'] ?? 1.2) / 100) / 12;
            $monthlyInsurance = ($input_data['insuranceRate'] ?? 1200) / 12;
            $monthlyHOA = $input_data['hoaFees'] ?? 0;
            
            $totalPayment = $monthlyPayment + $monthlyPropertyTax + $monthlyInsurance + $monthlyHOA;
            
            // Add PMI if less than 20%
            if ($percent < 20) {
                $totalPayment += $loanAmount * 0.005 / 12;
            }
            
            $scenarios[] = array(
                'downPaymentPercent' => $percent,
                'downPayment' => $targetDownPayment,
                'homePrice' => round($homePrice),
                'loanAmount' => round($loanAmount),
                'monthlyPayment' => round($totalPayment)
            );
        }
        
        return $scenarios;
    }
}