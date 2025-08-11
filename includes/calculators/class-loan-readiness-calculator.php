<?php
/**
 * Loan Readiness Score Calculator Class
 * 
 * UQUAL's proprietary holistic assessment tool
 */

if (!defined('ABSPATH')) {
    exit;
}

class UQUAL_Loan_Readiness_Calculator extends UQUAL_Base_Calculator {
    
    /**
     * Initialize calculator
     */
    protected function init() {
        $this->type = 'loan_readiness';
        $this->name = __('Loan Readiness Score Calculator', 'uqual-calculators');
        $this->description = __('Get your comprehensive loan readiness assessment with our proprietary scoring system that evaluates multiple financial factors.', 'uqual-calculators');
        
        $this->setup_input_fields();
        $this->setup_validation_rules();
    }
    
    /**
     * Setup input fields
     */
    private function setup_input_fields() {
        $this->input_fields = array(
            array(
                'name' => 'creditScore',
                'label' => __('Credit Score', 'uqual-calculators'),
                'type' => 'range',
                'min' => 300,
                'max' => 850,
                'step' => 1,
                'default' => 650,
                'required' => true,
                'help' => __('Your current FICO credit score (300-850)', 'uqual-calculators')
            ),
            array(
                'name' => 'monthlyIncome',
                'label' => __('Monthly Gross Income', 'uqual-calculators'),
                'type' => 'currency',
                'min' => 0,
                'step' => 100,
                'required' => true,
                'placeholder' => '5000',
                'help' => __('Your total monthly income before taxes', 'uqual-calculators')
            ),
            array(
                'name' => 'monthlyDebt',
                'label' => __('Monthly Debt Payments', 'uqual-calculators'),
                'type' => 'currency',
                'min' => 0,
                'step' => 50,
                'required' => true,
                'placeholder' => '1500',
                'help' => __('Total monthly debt obligations (credit cards, loans, etc.)', 'uqual-calculators')
            ),
            array(
                'name' => 'downPayment',
                'label' => __('Available Down Payment', 'uqual-calculators'),
                'type' => 'currency',
                'min' => 0,
                'step' => 1000,
                'required' => true,
                'placeholder' => '20000',
                'help' => __('Amount you have saved for down payment', 'uqual-calculators')
            ),
            array(
                'name' => 'homePrice',
                'label' => __('Target Home Price', 'uqual-calculators'),
                'type' => 'currency',
                'min' => 0,
                'step' => 5000,
                'required' => true,
                'placeholder' => '300000',
                'help' => __('Price range of homes you are considering', 'uqual-calculators')
            ),
            array(
                'name' => 'documentationReady',
                'label' => __('Documentation Readiness', 'uqual-calculators'),
                'type' => 'checkboxes',
                'options' => array(
                    'tax_returns' => __('2 years of tax returns', 'uqual-calculators'),
                    'pay_stubs' => __('Recent pay stubs', 'uqual-calculators'),
                    'bank_statements' => __('Bank statements', 'uqual-calculators'),
                    'employment_verification' => __('Employment verification', 'uqual-calculators'),
                    'asset_documentation' => __('Asset documentation', 'uqual-calculators')
                ),
                'help' => __('Check all documents you have ready', 'uqual-calculators')
            )
        );
    }
    
    /**
     * Setup validation rules
     */
    private function setup_validation_rules() {
        $this->validation_rules = array(
            function($input) {
                if ($input['monthlyIncome'] <= 0) {
                    return __('Monthly income must be greater than 0', 'uqual-calculators');
                }
                return true;
            },
            function($input) {
                if ($input['monthlyDebt'] >= $input['monthlyIncome']) {
                    return __('Monthly debt cannot exceed monthly income', 'uqual-calculators');
                }
                return true;
            },
            function($input) {
                if ($input['homePrice'] <= 0) {
                    return __('Home price must be greater than 0', 'uqual-calculators');
                }
                return true;
            }
        );
    }
    
    /**
     * Perform loan readiness calculation
     */
    public function calculate($input_data) {
        // Component weightings based on industry research
        $weights = array(
            'creditScore' => 0.30,
            'dtiRatio' => 0.30,
            'downPayment' => 0.30,
            'documentation' => 0.10
        );
        
        // Calculate individual components
        $components = array();
        
        // Credit Score Component (0-100 scale)
        $components['creditScore'] = $this->calculate_credit_component($input_data['creditScore']);
        
        // DTI Ratio Component
        $components['dtiRatio'] = $this->calculate_dti_component(
            $input_data['monthlyDebt'],
            $input_data['monthlyIncome']
        );
        
        // Down Payment Component
        $components['downPayment'] = $this->calculate_down_payment_component(
            $input_data['downPayment'],
            $input_data['homePrice']
        );
        
        // Documentation Component
        $components['documentation'] = $this->calculate_documentation_component(
            $input_data['documentationReady'] ?? array()
        );
        
        // Calculate weighted final score
        $finalScore = 0;
        foreach ($components as $component => $score) {
            $finalScore += $score * $weights[$component];
        }
        
        $finalScore = round($finalScore);
        
        // Get classification
        $classification = $this->get_score_classification($finalScore);
        
        // Generate recommendations
        $recommendations = $this->generate_detailed_recommendations(
            $finalScore,
            $components,
            $input_data
        );
        
        // Calculate additional insights
        $insights = $this->generate_insights($input_data, $components);
        
        return array(
            'score' => $finalScore,
            'components' => $components,
            'classification' => $classification,
            'recommendations' => $recommendations,
            'insights' => $insights,
            'input_summary' => $this->generate_input_summary($input_data)
        );
    }
    
    /**
     * Calculate credit score component
     */
    private function calculate_credit_component($creditScore) {
        // Convert 300-850 scale to 0-100
        $normalized = ($creditScore - 300) / 550 * 100;
        
        // Apply non-linear scaling for more realistic representation
        if ($creditScore >= 740) {
            // Excellent credit gets bonus
            $normalized = min(100, $normalized * 1.1);
        } elseif ($creditScore < 580) {
            // Poor credit gets penalty
            $normalized = $normalized * 0.8;
        }
        
        return min(100, max(0, round($normalized)));
    }
    
    /**
     * Calculate DTI ratio component
     */
    private function calculate_dti_component($monthlyDebt, $monthlyIncome) {
        if ($monthlyIncome <= 0) {
            return 0;
        }
        
        $dtiRatio = ($monthlyDebt / $monthlyIncome) * 100;
        
        // Lower DTI is better - inverse scoring
        if ($dtiRatio <= 20) {
            return 100;
        } elseif ($dtiRatio <= 28) {
            return 90;
        } elseif ($dtiRatio <= 36) {
            return 75;
        } elseif ($dtiRatio <= 43) {
            return 50;
        } elseif ($dtiRatio <= 50) {
            return 25;
        } else {
            // DTI over 50% is very poor
            return max(0, 100 - ($dtiRatio * 2));
        }
    }
    
    /**
     * Calculate down payment component
     */
    private function calculate_down_payment_component($downPayment, $homePrice) {
        if ($homePrice <= 0) {
            return 0;
        }
        
        $downPaymentRatio = ($downPayment / $homePrice) * 100;
        
        // Score based on down payment percentage
        if ($downPaymentRatio >= 20) {
            // 20% or more is excellent
            return min(100, 80 + $downPaymentRatio);
        } elseif ($downPaymentRatio >= 10) {
            // 10-20% is good
            return 60 + ($downPaymentRatio - 10) * 2;
        } elseif ($downPaymentRatio >= 5) {
            // 5-10% is acceptable
            return 40 + ($downPaymentRatio - 5) * 4;
        } elseif ($downPaymentRatio >= 3.5) {
            // 3.5% minimum for FHA
            return 30 + ($downPaymentRatio - 3.5) * 6.67;
        } else {
            // Less than 3.5% is poor
            return $downPaymentRatio * 8.57;
        }
    }
    
    /**
     * Calculate documentation component
     */
    private function calculate_documentation_component($documents) {
        if (!is_array($documents)) {
            return 0;
        }
        
        $requiredDocs = array(
            'tax_returns',
            'pay_stubs',
            'bank_statements',
            'employment_verification',
            'asset_documentation'
        );
        
        $completedCount = count(array_intersect($documents, $requiredDocs));
        $totalRequired = count($requiredDocs);
        
        return round(($completedCount / $totalRequired) * 100);
    }
    
    /**
     * Generate detailed recommendations
     */
    private function generate_detailed_recommendations($score, $components, $input_data) {
        $recommendations = array();
        
        // Always show CTA if score is below 80
        if ($score < 80) {
            $recommendations[] = array(
                'type' => 'cta',
                'title' => __('Get Professional Loan Readiness Help', 'uqual-calculators'),
                'description' => __('Our experts can help you improve your loan readiness score and qualify for better rates. Schedule a free consultation today.', 'uqual-calculators'),
                'action' => get_option('uqual_calc_cta_url', home_url('/consultation')),
                'action_text' => get_option('uqual_calc_default_cta_text', __('Schedule Free Consultation', 'uqual-calculators')),
                'priority' => 'high'
            );
        }
        
        // Credit score recommendations
        if ($components['creditScore'] < 70) {
            $recommendations[] = array(
                'type' => 'improvement',
                'title' => __('Improve Your Credit Score', 'uqual-calculators'),
                'description' => $this->get_credit_improvement_tips($input_data['creditScore']),
                'priority' => 'high'
            );
        }
        
        // DTI recommendations
        if ($components['dtiRatio'] < 70) {
            $dtiRatio = ($input_data['monthlyDebt'] / $input_data['monthlyIncome']) * 100;
            $recommendations[] = array(
                'type' => 'improvement',
                'title' => __('Lower Your Debt-to-Income Ratio', 'uqual-calculators'),
                'description' => $this->get_dti_improvement_tips($dtiRatio),
                'priority' => 'high'
            );
        }
        
        // Down payment recommendations
        if ($components['downPayment'] < 70) {
            $downPaymentPercent = ($input_data['downPayment'] / $input_data['homePrice']) * 100;
            $recommendations[] = array(
                'type' => 'improvement',
                'title' => __('Increase Your Down Payment', 'uqual-calculators'),
                'description' => $this->get_down_payment_tips($downPaymentPercent),
                'priority' => 'medium'
            );
        }
        
        // Documentation recommendations
        if ($components['documentation'] < 100) {
            $recommendations[] = array(
                'type' => 'action',
                'title' => __('Complete Your Documentation', 'uqual-calculators'),
                'description' => __('Gather all required documents including tax returns, pay stubs, and bank statements to speed up your loan application.', 'uqual-calculators'),
                'priority' => 'low'
            );
        }
        
        return $recommendations;
    }
    
    /**
     * Get credit improvement tips
     */
    private function get_credit_improvement_tips($creditScore) {
        if ($creditScore < 580) {
            return __('Your credit score needs significant improvement. Focus on paying off collections, reducing credit utilization below 30%, and making all payments on time. Consider credit repair services.', 'uqual-calculators');
        } elseif ($creditScore < 670) {
            return __('Work on reducing credit card balances, avoid new credit applications, and ensure all payments are made on time. Consider becoming an authorized user on a family member\'s account.', 'uqual-calculators');
        } else {
            return __('Continue making on-time payments and keep credit utilization low. Small improvements can lead to better loan terms.', 'uqual-calculators');
        }
    }
    
    /**
     * Get DTI improvement tips
     */
    private function get_dti_improvement_tips($dtiRatio) {
        if ($dtiRatio > 43) {
            return __('Your DTI is too high for most conventional loans. Focus on paying down existing debts or increasing your income. Consider debt consolidation to lower monthly payments.', 'uqual-calculators');
        } elseif ($dtiRatio > 36) {
            return __('Your DTI is acceptable but not ideal. Pay down credit cards and avoid taking on new debt. Even small reductions can improve your loan terms.', 'uqual-calculators');
        } else {
            return __('Your DTI is good but there\'s room for improvement. Lower debt payments will give you more borrowing power.', 'uqual-calculators');
        }
    }
    
    /**
     * Get down payment tips
     */
    private function get_down_payment_tips($downPaymentPercent) {
        if ($downPaymentPercent < 5) {
            return __('You need at least 3.5% for an FHA loan or 5% for conventional loans. Consider down payment assistance programs or gifts from family.', 'uqual-calculators');
        } elseif ($downPaymentPercent < 10) {
            return __('A larger down payment reduces your monthly payment and may eliminate PMI. Consider saving for a few more months or exploring down payment assistance.', 'uqual-calculators');
        } elseif ($downPaymentPercent < 20) {
            return __('Reaching 20% down payment eliminates PMI and provides better loan terms. Calculate if waiting to save more is worth the potential home price increases.', 'uqual-calculators');
        } else {
            return __('Your down payment is strong. Consider if you want to keep some funds for reserves or home improvements.', 'uqual-calculators');
        }
    }
    
    /**
     * Generate insights
     */
    private function generate_insights($input_data, $components) {
        $insights = array();
        
        // Calculate maximum affordable home price
        $maxAffordablePrice = $this->calculate_max_affordable_price(
            $input_data['monthlyIncome'],
            $input_data['monthlyDebt'],
            $input_data['downPayment']
        );
        
        $insights['maxAffordablePrice'] = $maxAffordablePrice;
        $insights['targetPriceAffordable'] = $input_data['homePrice'] <= $maxAffordablePrice;
        
        // Calculate required income for target home
        $requiredIncome = $this->calculate_required_income(
            $input_data['homePrice'],
            $input_data['downPayment'],
            $input_data['monthlyDebt']
        );
        
        $insights['requiredIncome'] = $requiredIncome;
        $insights['incomeGap'] = max(0, $requiredIncome - $input_data['monthlyIncome']);
        
        // Estimate time to improve
        $insights['estimatedImprovementTime'] = $this->estimate_improvement_time($components);
        
        return $insights;
    }
    
    /**
     * Calculate maximum affordable home price
     */
    private function calculate_max_affordable_price($monthlyIncome, $monthlyDebt, $downPayment) {
        // Use 28/36 rule
        $maxHousingPayment = $monthlyIncome * 0.28;
        $maxTotalDebt = $monthlyIncome * 0.36;
        $availableForHousing = $maxTotalDebt - $monthlyDebt;
        
        $affordablePayment = min($maxHousingPayment, $availableForHousing);
        
        // Assume 4.5% interest rate, 30-year loan
        $loanAmount = $this->calculate_loan_amount($affordablePayment * 0.75, 4.5, 30);
        
        return round($loanAmount + $downPayment);
    }
    
    /**
     * Calculate required income for target home
     */
    private function calculate_required_income($homePrice, $downPayment, $monthlyDebt) {
        $loanAmount = $homePrice - $downPayment;
        
        // Assume 4.5% interest rate, 30-year loan
        $monthlyPayment = $this->calculate_monthly_payment($loanAmount, 4.5, 30);
        
        // Add estimated taxes and insurance (25% of payment)
        $totalHousingPayment = $monthlyPayment * 1.25;
        
        // Use 28/36 rule to calculate required income
        $requiredFromHousing = $totalHousingPayment / 0.28;
        $requiredFromDebt = ($totalHousingPayment + $monthlyDebt) / 0.36;
        
        return round(max($requiredFromHousing, $requiredFromDebt));
    }
    
    /**
     * Estimate time to improve score
     */
    private function estimate_improvement_time($components) {
        $months = 0;
        
        if ($components['creditScore'] < 70) {
            $months = max($months, 6); // Credit improvement takes 6+ months
        }
        
        if ($components['dtiRatio'] < 70) {
            $months = max($months, 3); // Debt reduction takes 3+ months
        }
        
        if ($components['downPayment'] < 70) {
            $months = max($months, 12); // Saving for down payment takes 12+ months
        }
        
        if ($components['documentation'] < 100) {
            $months = max($months, 1); // Document gathering takes 1 month
        }
        
        return $months;
    }
    
    /**
     * Generate input summary
     */
    private function generate_input_summary($input_data) {
        return array(
            'creditScore' => $input_data['creditScore'],
            'monthlyIncome' => $this->format_currency($input_data['monthlyIncome']),
            'monthlyDebt' => $this->format_currency($input_data['monthlyDebt']),
            'dtiRatio' => $this->format_percentage(($input_data['monthlyDebt'] / $input_data['monthlyIncome']) * 100),
            'downPayment' => $this->format_currency($input_data['downPayment']),
            'homePrice' => $this->format_currency($input_data['homePrice']),
            'downPaymentPercent' => $this->format_percentage(($input_data['downPayment'] / $input_data['homePrice']) * 100),
            'documentationComplete' => count($input_data['documentationReady'] ?? array()) === 5
        );
    }
    
    /**
     * Group fields into steps for mobile wizard
     */
    protected function group_fields_into_steps() {
        return array(
            array(
                'label' => __('Income & Credit', 'uqual-calculators'),
                'fields' => array_slice($this->input_fields, 0, 2)
            ),
            array(
                'label' => __('Debt & Savings', 'uqual-calculators'),
                'fields' => array_slice($this->input_fields, 2, 2)
            ),
            array(
                'label' => __('Home Goals', 'uqual-calculators'),
                'fields' => array_slice($this->input_fields, 4, 1)
            ),
            array(
                'label' => __('Documentation', 'uqual-calculators'),
                'fields' => array_slice($this->input_fields, 5, 1)
            )
        );
    }
}