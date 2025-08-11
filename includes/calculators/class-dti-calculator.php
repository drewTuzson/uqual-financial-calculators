<?php
/**
 * DTI Calculator Class
 * 
 * Advanced Debt-to-Income Ratio Calculator
 */

if (!defined('ABSPATH')) {
    exit;
}

class UQUAL_DTI_Calculator extends UQUAL_Base_Calculator {
    
    /**
     * Initialize calculator
     */
    protected function init() {
        $this->type = 'dti';
        $this->name = __('Advanced DTI Calculator', 'uqual-calculators');
        $this->description = __('Calculate your debt-to-income ratio to understand your borrowing capacity and loan qualification status.', 'uqual-calculators');
        
        $this->setup_input_fields();
    }
    
    /**
     * Setup input fields
     */
    private function setup_input_fields() {
        $this->input_fields = array(
            array(
                'name' => 'incomeFrequency',
                'label' => __('Income Frequency', 'uqual-calculators'),
                'type' => 'select',
                'options' => array(
                    'annual' => __('Annual', 'uqual-calculators'),
                    'monthly' => __('Monthly', 'uqual-calculators')
                ),
                'default' => 'annual',
                'required' => true
            ),
            array(
                'name' => 'grossIncome',
                'label' => __('Gross Income', 'uqual-calculators'),
                'type' => 'currency',
                'min' => 0,
                'required' => true,
                'placeholder' => '75000',
                'help' => __('Your gross income before taxes', 'uqual-calculators')
            ),
            array(
                'name' => 'housingPayment',
                'label' => __('Housing Payment', 'uqual-calculators'),
                'type' => 'currency',
                'min' => 0,
                'placeholder' => '1500',
                'help' => __('Current or proposed monthly housing payment (rent/mortgage)', 'uqual-calculators')
            ),
            array(
                'name' => 'creditCardMinimums',
                'label' => __('Credit Card Minimum Payments', 'uqual-calculators'),
                'type' => 'currency',
                'min' => 0,
                'placeholder' => '200',
                'help' => __('Total minimum monthly credit card payments', 'uqual-calculators')
            ),
            array(
                'name' => 'carLoans',
                'label' => __('Car Loan Payments', 'uqual-calculators'),
                'type' => 'currency',
                'min' => 0,
                'placeholder' => '400',
                'help' => __('Total monthly car loan payments', 'uqual-calculators')
            ),
            array(
                'name' => 'studentLoans',
                'label' => __('Student Loan Payments', 'uqual-calculators'),
                'type' => 'currency',
                'min' => 0,
                'placeholder' => '300',
                'help' => __('Total monthly student loan payments', 'uqual-calculators')
            ),
            array(
                'name' => 'personalLoans',
                'label' => __('Personal Loan Payments', 'uqual-calculators'),
                'type' => 'currency',
                'min' => 0,
                'placeholder' => '0',
                'help' => __('Total monthly personal loan payments', 'uqual-calculators')
            ),
            array(
                'name' => 'otherDebts',
                'label' => __('Other Monthly Debts', 'uqual-calculators'),
                'type' => 'currency',
                'min' => 0,
                'placeholder' => '0',
                'help' => __('Any other monthly debt obligations', 'uqual-calculators')
            )
        );
    }
    
    /**
     * Perform DTI calculation
     */
    public function calculate($input_data) {
        // Monthly gross income calculation
        $monthlyIncome = 0;
        if ($input_data['incomeFrequency'] === 'annual') {
            $monthlyIncome = $input_data['grossIncome'] / 12;
        } else {
            $monthlyIncome = $input_data['grossIncome'];
        }
        
        // Total monthly debt obligations
        $totalDebt = 
            ($input_data['housingPayment'] ?? 0) +
            ($input_data['creditCardMinimums'] ?? 0) +
            ($input_data['carLoans'] ?? 0) +
            ($input_data['studentLoans'] ?? 0) +
            ($input_data['personalLoans'] ?? 0) +
            ($input_data['otherDebts'] ?? 0);
        
        // DTI Calculation
        $dtiRatio = $monthlyIncome > 0 ? ($totalDebt / $monthlyIncome) * 100 : 0;
        
        // Classification based on lending standards
        $classification = $this->classify_dti($dtiRatio);
        
        // Generate recommendations
        $recommendations = $this->generate_dti_recommendations($dtiRatio);
        
        return array(
            'ratio' => round($dtiRatio, 2),
            'classification' => $classification,
            'monthlyIncome' => $monthlyIncome,
            'totalDebt' => $totalDebt,
            'recommendations' => $recommendations,
            'breakdown' => array(
                'housing' => $input_data['housingPayment'] ?? 0,
                'creditCards' => $input_data['creditCardMinimums'] ?? 0,
                'carLoans' => $input_data['carLoans'] ?? 0,
                'studentLoans' => $input_data['studentLoans'] ?? 0,
                'personalLoans' => $input_data['personalLoans'] ?? 0,
                'other' => $input_data['otherDebts'] ?? 0
            )
        );
    }
    
    /**
     * Classify DTI ratio
     */
    private function classify_dti($dtiRatio) {
        if ($dtiRatio <= 28) return __('Excellent', 'uqual-calculators');
        elseif ($dtiRatio <= 36) return __('Good', 'uqual-calculators');
        elseif ($dtiRatio <= 43) return __('Acceptable', 'uqual-calculators');
        else return __('High Risk', 'uqual-calculators');
    }
    
    /**
     * Generate DTI recommendations
     */
    private function generate_dti_recommendations($dtiRatio) {
        $recommendations = array();
        
        if ($dtiRatio > 43) {
            $recommendations[] = __('Your DTI is too high for most conventional loans. Focus on paying down existing debt.', 'uqual-calculators');
            $recommendations[] = __('Consider debt consolidation to lower monthly payments.', 'uqual-calculators');
            $recommendations[] = __('Look for ways to increase your income through side jobs or salary negotiation.', 'uqual-calculators');
        } elseif ($dtiRatio > 36) {
            $recommendations[] = __('Your DTI is acceptable but could be better. Work on reducing credit card balances.', 'uqual-calculators');
            $recommendations[] = __('Avoid taking on new debt before applying for a mortgage.', 'uqual-calculators');
        } elseif ($dtiRatio > 28) {
            $recommendations[] = __('Your DTI is good. Small improvements could help you qualify for better rates.', 'uqual-calculators');
        } else {
            $recommendations[] = __('Excellent DTI ratio! You should qualify for the best loan terms.', 'uqual-calculators');
            $recommendations[] = __('Maintain your current financial discipline.', 'uqual-calculators');
        }
        
        return $recommendations;
    }
}