<?php
/**
 * Credit Score Improvement Simulator Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class UQUAL_Credit_Simulator extends UQUAL_Base_Calculator {
    
    protected function init() {
        $this->type = 'credit_simulator';
        $this->name = __('Credit Score Improvement Simulator', 'uqual-calculators');
        $this->description = __('Simulate how different actions can improve your credit score over time.', 'uqual-calculators');
        
        $this->setup_input_fields();
    }
    
    private function setup_input_fields() {
        $this->input_fields = array(
            array(
                'name' => 'currentScore',
                'label' => __('Current Credit Score', 'uqual-calculators'),
                'type' => 'range',
                'min' => 300,
                'max' => 850,
                'default' => 650,
                'required' => true
            ),
            array(
                'name' => 'actions',
                'label' => __('Improvement Actions', 'uqual-calculators'),
                'type' => 'checkboxes',
                'options' => array(
                    'payOffCollection' => __('Pay off collections', 'uqual-calculators'),
                    'reduceUtilization10' => __('Reduce credit utilization to 10%', 'uqual-calculators'),
                    'reduceUtilization30' => __('Reduce credit utilization to 30%', 'uqual-calculators'),
                    'payOnTime6Months' => __('Make on-time payments for 6 months', 'uqual-calculators'),
                    'addAuthorizedUser' => __('Become authorized user on aged account', 'uqual-calculators'),
                    'payOffCreditCard' => __('Pay off credit card balances', 'uqual-calculators')
                )
            )
        );
    }
    
    public function calculate($input_data) {
        $currentScore = $input_data['currentScore'];
        $actions = $input_data['actions'] ?? array();
        $projectedScore = $currentScore;
        $timelineMonths = 0;
        
        // Impact factors based on FICO weighting research
        $impactFactors = array(
            'payOffCollection' => array('points' => rand(15, 25), 'months' => 2),
            'reduceUtilization10' => array('points' => rand(8, 15), 'months' => 1),
            'reduceUtilization30' => array('points' => rand(20, 30), 'months' => 1),
            'payOnTime6Months' => array('points' => rand(5, 15), 'months' => 6),
            'addAuthorizedUser' => array('points' => rand(10, 20), 'months' => 2),
            'payOffCreditCard' => array('points' => rand(8, 20), 'months' => 1)
        );
        
        $actionDetails = array();
        
        foreach ($actions as $action) {
            if (isset($impactFactors[$action])) {
                $impact = $impactFactors[$action];
                $projectedScore += $impact['points'];
                $timelineMonths = max($timelineMonths, $impact['months']);
                
                $actionDetails[] = array(
                    'type' => $action,
                    'title' => $this->get_action_title($action),
                    'impact' => $impact['points'],
                    'description' => $this->get_action_description($action)
                );
            }
        }
        
        // Cap at 850
        $projectedScore = min(850, $projectedScore);
        
        return array(
            'currentScore' => $currentScore,
            'projectedScore' => $projectedScore,
            'improvement' => $projectedScore - $currentScore,
            'timelineMonths' => max($timelineMonths, 3),
            'actions' => $actionDetails
        );
    }
    
    private function get_action_title($action) {
        $titles = array(
            'payOffCollection' => __('Pay Off Collections', 'uqual-calculators'),
            'reduceUtilization10' => __('Reduce Utilization to 10%', 'uqual-calculators'),
            'reduceUtilization30' => __('Reduce Utilization to 30%', 'uqual-calculators'),
            'payOnTime6Months' => __('6 Months On-Time Payments', 'uqual-calculators'),
            'addAuthorizedUser' => __('Authorized User Status', 'uqual-calculators'),
            'payOffCreditCard' => __('Pay Off Credit Cards', 'uqual-calculators')
        );
        return $titles[$action] ?? $action;
    }
    
    private function get_action_description($action) {
        $descriptions = array(
            'payOffCollection' => __('Paying off collection accounts can significantly improve your score.', 'uqual-calculators'),
            'reduceUtilization10' => __('Keeping credit utilization below 10% shows excellent credit management.', 'uqual-calculators'),
            'reduceUtilization30' => __('Reducing credit utilization below 30% is a key factor in credit scoring.', 'uqual-calculators'),
            'payOnTime6Months' => __('Consistent on-time payments demonstrate creditworthiness.', 'uqual-calculators'),
            'addAuthorizedUser' => __('Being added to an account with good payment history can boost your score.', 'uqual-calculators'),
            'payOffCreditCard' => __('Eliminating credit card debt improves your utilization ratio.', 'uqual-calculators')
        );
        return $descriptions[$action] ?? '';
    }
}