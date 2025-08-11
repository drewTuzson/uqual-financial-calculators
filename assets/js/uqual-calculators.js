/**
 * UQUAL Financial Calculators - Main JavaScript
 * 
 * Handles all calculator interactions, form validation, and AJAX requests
 */

(function($) {
    'use strict';
    
    // Calculator Manager Class
    class UQUALCalculatorManager {
        constructor() {
            this.calculators = new Map();
            this.currentSessions = new Map();
            this.init();
        }
        
        init() {
            this.bindEvents();
            this.initializeCalculators();
            this.setupRangeSliders();
            this.setupStepWizards();
        }
        
        bindEvents() {
            // Use try-catch for all event binding
            try {
                $(document).on('submit', '.uqual-calculator-form', this.handleFormSubmit.bind(this));
                $(document).on('click', '.uqual-reset-btn', this.handleFormReset.bind(this));
                $(document).on('click', '.uqual-calculate-btn', this.handleCalculateClick.bind(this));
                $(document).on('input', '.uqual-range-input', this.updateRangeValue.bind(this));
                $(document).on('change', '.uqual-calculator-form input, .uqual-calculator-form select', this.trackInputChange.bind(this));
                $(document).on('click', '.step-next', this.nextStep.bind(this));
                $(document).on('click', '.step-prev', this.prevStep.bind(this));
                $(document).on('click', '[data-event="cta_click"]', this.trackCTAClick.bind(this));
                
                // Track form start
                $(document).on('focus', '.uqual-calculator-form input:first', this.trackFormStart.bind(this));
                
                // Track form abandonment
                $(window).on('beforeunload', this.trackFormAbandon.bind(this));
                
                console.log('UQUAL Calculator events bound successfully');
            } catch (error) {
                console.error('Error binding calculator events:', error);
            }
        }
        
        initializeCalculators() {
            $('.uqual-calculator-wrapper').each((index, element) => {
                const $wrapper = $(element);
                const calculatorType = $wrapper.data('calculator-type');
                const sessionId = $wrapper.data('session-id');
                
                if (calculatorType && sessionId) {
                    this.calculators.set(sessionId, {
                        type: calculatorType,
                        element: $wrapper,
                        currentStep: 0,
                        totalSteps: $wrapper.find('.step-panel').length || 1
                    });
                }
            });
        }
        
        setupRangeSliders() {
            $('.uqual-range-input').each(function() {
                const $slider = $(this);
                const $output = $slider.siblings('.uqual-range-value');
                
                // Initialize value
                $output.text($slider.val());
                
                // Update on input
                $slider.on('input', function() {
                    let value = $(this).val();
                    
                    // Format based on field name
                    if ($(this).attr('name').includes('Score')) {
                        value = parseInt(value);
                    } else if ($(this).attr('name').toLowerCase().includes('rate')) {
                        value = parseFloat(value).toFixed(1) + '%';
                    } else if ($(this).attr('name').toLowerCase().includes('percent')) {
                        value = parseFloat(value).toFixed(1) + '%';
                    } else {
                        value = '$' + parseInt(value).toLocaleString();
                    }
                    
                    $output.text(value);
                });
            });
        }
        
        setupStepWizards() {
            // Mobile detection and step wizard initialization
            if (this.isMobile()) {
                $('.uqual-calculator').each(function() {
                    const $calc = $(this);
                    const $standardFields = $calc.find('.uqual-field');
                    const $stepWizard = $calc.find('.uqual-step-wizard');
                    
                    if ($stepWizard.length > 0) {
                        $standardFields.hide();
                        $stepWizard.show();
                    }
                });
            }
        }
        
        handleFormSubmit(event) {
            event.preventDefault();
            
            try {
                const $form = $(event.target);
                const $wrapper = $form.closest('.uqual-calculator-wrapper');
                const sessionId = $wrapper.data('session-id');
                const calculatorType = $wrapper.data('calculator-type');
                
                if (!sessionId || !calculatorType) {
                    this.showError('Calculator not properly initialized. Please refresh the page.');
                    return;
                }
                
                this.processCalculation($form, $wrapper, sessionId, calculatorType);
            } catch (error) {
                console.error('Error in form submission:', error);
                this.showError('An error occurred during form submission. Please try again.');
            }
        }
        
        handleCalculateClick(event) {
            event.preventDefault();
            
            try {
                const $button = $(event.target);
                const $form = $button.closest('.uqual-calculator-form');
                const $wrapper = $form.closest('.uqual-calculator-wrapper');
                
                if ($form.length === 0) {
                    this.showError('Calculator form not found. Please refresh the page.');
                    return;
                }
                
                // Trigger form submission
                $form.trigger('submit');
            } catch (error) {
                console.error('Error in calculate button click:', error);
                this.showError('An error occurred. Please try again.');
            }
        }
        
        processCalculation($form, $wrapper, sessionId, calculatorType) {
            // Show loading state
            this.showLoading($wrapper);
            
            // Collect form data
            const formData = this.collectFormData($form);
            
            // Validate data
            if (!this.validateFormData(formData, calculatorType)) {
                this.hideLoading($wrapper);
                return;
            }
            
            // Submit calculation
            this.submitCalculation(sessionId, calculatorType, formData, $wrapper);
        }
        
        handleFormReset(event) {
            event.preventDefault();
            
            const $form = $(event.target).closest('.uqual-calculator-form');
            const $wrapper = $form.closest('.uqual-calculator-wrapper');
            
            // Reset form
            $form[0].reset();
            
            // Update range displays
            $form.find('.uqual-range-input').each(function() {
                $(this).trigger('input');
            });
            
            // Hide results
            $wrapper.find('.uqual-calculator-results').hide();
            
            // Reset step wizard
            if (this.isMobile()) {
                this.goToStep($wrapper, 0);
            }
            
            // Track reset
            this.trackEvent($wrapper.data('session-id'), 'form_reset', {
                calculator_type: $wrapper.data('calculator-type')
            });
        }
        
        collectFormData($form) {
            const data = {};
            
            $form.find('input, select, textarea').each(function() {
                const $field = $(this);
                const name = $field.attr('name');
                const type = $field.attr('type');
                
                if (!name) return;
                
                if (type === 'checkbox') {
                    if ($field.is(':checked')) {
                        if ($field.attr('name').endsWith('[]')) {
                            const baseName = name.replace('[]', '');
                            if (!data[baseName]) data[baseName] = [];
                            data[baseName].push($field.val());
                        } else {
                            data[name] = true;
                        }
                    }
                } else if (type === 'radio') {
                    if ($field.is(':checked')) {
                        data[name] = $field.val();
                    }
                } else {
                    const value = $field.val();
                    if (value !== '') {
                        // Convert numbers
                        if (type === 'number' || type === 'range') {
                            data[name] = parseFloat(value) || 0;
                        } else {
                            data[name] = value;
                        }
                    }
                }\n            });\n            \n            return data;\n        }\n        \n        validateFormData(data, calculatorType) {\n            // Basic validation - can be extended per calculator type\n            const requiredFields = this.getRequiredFields(calculatorType);\n            \n            for (const field of requiredFields) {\n                if (!data[field] || data[field] === '' || data[field] === 0) {\n                    this.showError(`Please fill in the required field: ${field}`);\n                    return false;\n                }\n            }\n            \n            return true;\n        }\n        \n        getRequiredFields(calculatorType) {\n            const fieldMap = {\n                'loan_readiness': ['creditScore', 'monthlyIncome', 'monthlyDebt', 'downPayment', 'homePrice'],\n                'dti': ['grossIncome', 'incomeFrequency'],\n                'affordability': ['grossIncome', 'downPayment', 'interestRate'],\n                'credit_simulator': ['currentScore'],\n                'savings': ['homePrice', 'monthlyDeposit']\n            };\n            \n            return fieldMap[calculatorType] || [];\n        }\n        \n        submitCalculation(sessionId, calculatorType, formData, $wrapper) {\n            $.ajax({\n                url: uqual_calc_ajax.ajax_url,\n                type: 'POST',\n                data: {\n                    action: 'uqual_calculate',\n                    nonce: uqual_calc_ajax.nonce,\n                    calculator_type: calculatorType,\n                    input_data: formData,\n                    session_id: sessionId\n                },\n                success: (response) => {\n                    this.hideLoading($wrapper);\n                    \n                    if (response.success) {\n                        this.displayResults($wrapper, response.data);\n                        this.trackEvent(sessionId, 'calculation_complete', {\n                            calculator_type: calculatorType,\n                            success: true\n                        });\n                    } else {\n                        this.showError(response.data.message || 'Calculation failed');\n                        this.trackEvent(sessionId, 'calculation_error', {\n                            calculator_type: calculatorType,\n                            error: response.data.message\n                        });\n                    }\n                },\n                error: (xhr, status, error) => {\n                    this.hideLoading($wrapper);\n                    this.showError('Network error occurred. Please try again.');\n                    this.trackEvent(sessionId, 'calculation_error', {\n                        calculator_type: calculatorType,\n                        error: 'Network error'\n                    });\n                }\n            });\n        }\n        \n        displayResults($wrapper, data) {\n            const $results = $wrapper.find('.uqual-calculator-results');\n            const $content = $results.find('.uqual-results-content');\n            \n            // Insert formatted results\n            if (data.formatted_results) {\n                $content.html(data.formatted_results);\n            } else {\n                $content.html(this.formatBasicResults(data.results));\n            }\n            \n            // Show results with animation\n            $results.slideDown(300);\n            \n            // Scroll to results\n            $('html, body').animate({\n                scrollTop: $results.offset().top - 100\n            }, 500);\n            \n            // Initialize result interactions\n            this.initializeResultInteractions($wrapper);\n        }\n        \n        formatBasicResults(results) {\n            let html = '<div class=\"basic-results\">';\n            \n            for (const [key, value] of Object.entries(results)) {\n                if (typeof value === 'object') continue;\n                \n                html += `<div class=\"result-item\">`;\n                html += `<span class=\"result-label\">${this.formatLabel(key)}:</span>`;\n                html += `<span class=\"result-value\">${this.formatValue(key, value)}</span>`;\n                html += `</div>`;\n            }\n            \n            html += '</div>';\n            return html;\n        }\n        \n        formatLabel(key) {\n            return key.replace(/([A-Z])/g, ' $1')\n                     .replace(/^./, str => str.toUpperCase());\n        }\n        \n        formatValue(key, value) {\n            if (key.toLowerCase().includes('price') || \n                key.toLowerCase().includes('amount') || \n                key.toLowerCase().includes('payment')) {\n                return '$' + parseFloat(value).toLocaleString();\n            }\n            \n            if (key.toLowerCase().includes('rate') || \n                key.toLowerCase().includes('ratio') ||\n                key.toLowerCase().includes('percent')) {\n                return parseFloat(value).toFixed(2) + '%';\n            }\n            \n            if (key.toLowerCase().includes('score')) {\n                return Math.round(parseFloat(value));\n            }\n            \n            return value;\n        }\n        \n        initializeResultInteractions($wrapper) {\n            // Initialize any charts or interactive elements\n            const $gauge = $wrapper.find('#score-gauge');\n            if ($gauge.length && typeof Chart !== 'undefined') {\n                // Chart initialization would be done here\n                // This is handled in the PHP-generated JavaScript\n            }\n            \n            // Set up CTA tracking\n            $wrapper.find('[data-event=\"cta_click\"]').off('click.cta').on('click.cta', (e) => {\n                this.trackCTAClick(e);\n            });\n        }\n        \n        // Step Wizard Methods\n        nextStep(event) {\n            event.preventDefault();\n            const $wrapper = $(event.target).closest('.uqual-calculator-wrapper');\n            const sessionId = $wrapper.data('session-id');\n            const calculator = this.calculators.get(sessionId);\n            \n            if (calculator && calculator.currentStep < calculator.totalSteps - 1) {\n                this.goToStep($wrapper, calculator.currentStep + 1);\n            }\n        }\n        \n        prevStep(event) {\n            event.preventDefault();\n            const $wrapper = $(event.target).closest('.uqual-calculator-wrapper');\n            const sessionId = $wrapper.data('session-id');\n            const calculator = this.calculators.get(sessionId);\n            \n            if (calculator && calculator.currentStep > 0) {\n                this.goToStep($wrapper, calculator.currentStep - 1);\n            }\n        }\n        \n        goToStep($wrapper, stepIndex) {\n            const sessionId = $wrapper.data('session-id');\n            const calculator = this.calculators.get(sessionId);\n            \n            if (!calculator) return;\n            \n            // Update step indicators\n            $wrapper.find('.step-indicator').removeClass('active')\n                    .eq(stepIndex).addClass('active');\n            \n            // Update step panels\n            $wrapper.find('.step-panel').removeClass('active')\n                    .eq(stepIndex).addClass('active');\n            \n            // Update navigation buttons\n            const $prevBtn = $wrapper.find('.step-prev');\n            const $nextBtn = $wrapper.find('.step-next');\n            \n            $prevBtn.toggle(stepIndex > 0);\n            \n            if (stepIndex === calculator.totalSteps - 1) {\n                $nextBtn.text('Calculate').removeClass('step-next').addClass('uqual-calculate-btn');\n            } else {\n                $nextBtn.text('Next').removeClass('uqual-calculate-btn').addClass('step-next');\n            }\n            \n            // Update calculator state\n            calculator.currentStep = stepIndex;\n            this.calculators.set(sessionId, calculator);\n            \n            // Track step change\n            this.trackEvent(sessionId, 'step_change', {\n                calculator_type: calculator.type,\n                step: stepIndex,\n                step_name: $wrapper.find('.step-indicator').eq(stepIndex).find('.step-label').text()\n            });\n        }\n        \n        // Event Tracking Methods\n        trackFormStart(event) {\n            const $wrapper = $(event.target).closest('.uqual-calculator-wrapper');\n            const sessionId = $wrapper.data('session-id');\n            \n            this.trackEvent(sessionId, 'form_start', {\n                calculator_type: $wrapper.data('calculator-type'),\n                timestamp: new Date().toISOString()\n            });\n        }\n        \n        trackInputChange(event) {\n            const $wrapper = $(event.target).closest('.uqual-calculator-wrapper');\n            const sessionId = $wrapper.data('session-id');\n            const $field = $(event.target);\n            \n            // Throttle input change events\n            clearTimeout(this.inputChangeTimer);\n            this.inputChangeTimer = setTimeout(() => {\n                this.trackEvent(sessionId, 'input_change', {\n                    calculator_type: $wrapper.data('calculator-type'),\n                    field_name: $field.attr('name'),\n                    field_type: $field.attr('type')\n                });\n            }, 1000);\n        }\n        \n        trackCTAClick(event) {\n            const $cta = $(event.target);\n            const $wrapper = $cta.closest('.uqual-calculator-wrapper');\n            const sessionId = $wrapper.data('session-id');\n            \n            this.trackEvent(sessionId, 'cta_click', {\n                calculator_type: $wrapper.data('calculator-type'),\n                cta_text: $cta.text(),\n                cta_url: $cta.attr('href'),\n                timestamp: new Date().toISOString()\n            });\n            \n            // Send conversion tracking\n            $.ajax({\n                url: uqual_calc_ajax.ajax_url,\n                type: 'POST',\n                data: {\n                    action: 'uqual_track_conversion',\n                    nonce: uqual_calc_ajax.nonce,\n                    session_id: sessionId,\n                    calculator_type: $wrapper.data('calculator-type')\n                }\n            });\n        }\n        \n        trackFormAbandon(event) {\n            $('.uqual-calculator-wrapper').each((index, element) => {\n                const $wrapper = $(element);\n                const sessionId = $wrapper.data('session-id');\n                const $form = $wrapper.find('.uqual-calculator-form');\n                \n                // Check if form has data but no results\n                if ($form.find('input:filled, select:not([value=\"\"]), textarea:filled').length > 0 &&\n                    !$wrapper.find('.uqual-calculator-results').is(':visible')) {\n                    \n                    this.trackEvent(sessionId, 'form_abandon', {\n                        calculator_type: $wrapper.data('calculator-type'),\n                        abandonment_point: 'page_unload'\n                    });\n                }\n            });\n        }\n        \n        trackEvent(sessionId, eventType, eventData) {\n            if (!sessionId) return;\n            \n            $.ajax({\n                url: uqual_calc_ajax.ajax_url,\n                type: 'POST',\n                data: {\n                    action: 'uqual_track_event',\n                    nonce: uqual_calc_ajax.nonce,\n                    session_id: sessionId,\n                    event_type: eventType,\n                    event_data: eventData\n                }\n            });\n        }\n        \n        // Utility Methods\n        showLoading($wrapper) {\n            const $results = $wrapper.find('.uqual-calculator-results');\n            $results.find('.uqual-results-loading').show();\n            $results.find('.uqual-results-content').hide();\n            $results.show();\n        }\n        \n        hideLoading($wrapper) {\n            const $results = $wrapper.find('.uqual-calculator-results');\n            $results.find('.uqual-results-loading').hide();\n            $results.find('.uqual-results-content').show();\n        }\n        \n        showError(message) {\n            // Create or update error display\n            let $error = $('.uqual-error-message');\n            if ($error.length === 0) {\n                $error = $('<div class=\"uqual-error-message uqual-calculator-error\"></div>');\n                $('body').append($error);\n            }\n            \n            $error.text(message).fadeIn();\n            \n            // Auto-hide after 5 seconds\n            setTimeout(() => {\n                $error.fadeOut();\n            }, 5000);\n        }\n        \n        updateRangeValue(event) {\n            const $slider = $(event.target);\n            const $output = $slider.siblings('.uqual-range-value');\n            $output.text($slider.val());\n        }\n        \n        isMobile() {\n            return window.innerWidth <= 768;\n        }\n    }\n    \n    // Utility functions for number formatting\n    window.UQUALUtils = {\n        formatCurrency: function(value, decimals = 0) {\n            return '$' + parseFloat(value).toLocaleString('en-US', {\n                minimumFractionDigits: decimals,\n                maximumFractionDigits: decimals\n            });\n        },\n        \n        formatPercentage: function(value, decimals = 2) {\n            return parseFloat(value).toFixed(decimals) + '%';\n        },\n        \n        formatNumber: function(value, decimals = 0) {\n            return parseFloat(value).toLocaleString('en-US', {\n                minimumFractionDigits: decimals,\n                maximumFractionDigits: decimals\n            });\n        }\n    };\n    \n    // Initialize when document is ready\n    $(document).ready(function() {\n        window.uqualCalculatorManager = new UQUALCalculatorManager();\n        \n        // Custom event for when calculators are initialized\n        $(document).trigger('uqual:calculators:initialized');\n    });\n    \n    // Re-initialize on AJAX complete (for dynamic content)\n    $(document).ajaxComplete(function() {\n        if ($('.uqual-calculator-wrapper').length > 0) {\n            setTimeout(() => {\n                if (window.uqualCalculatorManager) {\n                    window.uqualCalculatorManager.initializeCalculators();\n                    window.uqualCalculatorManager.setupRangeSliders();\n                    window.uqualCalculatorManager.setupStepWizards();\n                }\n            }, 100);\n        }\n    });\n    \n})(jQuery);"
}]