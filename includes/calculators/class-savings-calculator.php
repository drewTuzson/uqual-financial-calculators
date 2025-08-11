<?php
/**
 * Down Payment Savings Calculator Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class UQUAL_Savings_Calculator extends UQUAL_Base_Calculator {
    
    protected function init() {
        $this->type = 'savings';
        $this->name = __('Down Payment Savings Calculator', 'uqual-calculators');
        $this->description = __('Plan your down payment savings strategy with compound interest calculations.', 'uqual-calculators');
        
        $this->setup_input_fields();
    }
    
    private function setup_input_fields() {
        $this->input_fields = array(
            array(
                'name' => 'homePrice',
                'label' => __('Target Home Price', 'uqual-calculators'),
                'type' => 'currency',
                'required' => true,
                'placeholder' => '300000'
            ),
            array(
                'name' => 'downPaymentPercent',
                'label' => __('Down Payment Percentage', 'uqual-calculators'),
                'type' => 'range',
                'min' => 3.5,
                'max' => 30,
                'step' => 0.5,
                'default' => 20
            ),
            array(
                'name' => 'currentSavings',
                'label' => __('Current Savings', 'uqual-calculators'),
                'type' => 'currency',
                'placeholder' => '5000'
            ),
            array(
                'name' => 'monthlyDeposit',
                'label' => __('Monthly Savings', 'uqual-calculators'),
                'type' => 'currency',
                'required' => true,
                'placeholder' => '500'
            ),
            array(
                'name' => 'interestRate',
                'label' => __('Interest Rate (%)', 'uqual-calculators'),
                'type' => 'number',
                'min' => 0,
                'max' => 10,
                'step' => 0.1,
                'default' => 2.5
            ),
            array(
                'name' => 'timelineMonths',
                'label' => __('Target Timeline (Months)', 'uqual-calculators'),
                'type' => 'integer',
                'min' => 1,
                'max' => 600,
                'default' => 36
            )
        );
    }
    
    public function calculate($input_data) {
        $targetAmount = $input_data['homePrice'] * ($input_data['downPaymentPercent'] / 100);
        $currentSavings = $input_data['currentSavings'] ?? 0;
        $monthlyDeposit = $input_data['monthlyDeposit'] ?? 0;
        $annualRate = ($input_data['interestRate'] ?? 2.5) / 100;
        $monthlyRate = $annualRate / 12;
        
        $balance = $currentSavings;
        $months = 0;
        $maxMonths = 600;
        
        // Calculate time to reach goal
        while ($balance < $targetAmount && $months < $maxMonths) {
            $balance = $balance * (1 + $monthlyRate) + $monthlyDeposit;
            $months++;
        }
        
        if ($months >= $maxMonths) {
            // Calculate required payment
            $timelineMonths = $input_data['timelineMonths'] ?? 36;
            $requiredPayment = ($targetAmount - $currentSavings) / 
                ((pow(1 + $monthlyRate, $timelineMonths) - 1) / $monthlyRate * 
                 pow(1 + $monthlyRate, $timelineMonths));
            
            return array(
                'canReachGoal' => false,
                'requiredMonthlyPayment' => round($requiredPayment),
                'targetAmount' => $targetAmount,
                'currentShortfall' => $targetAmount - $currentSavings
            );
        }
        
        return array(
            'canReachGoal' => true,
            'monthsToGoal' => $months,
            'yearsToGoal' => round($months / 12, 1),
            'finalAmount' => round($balance),
            'targetAmount' => $targetAmount,
            'totalInterestEarned' => round($balance - $currentSavings - ($monthlyDeposit * $months))
        );
    }
}