<?php
/**
 * Plugin Name: Forminator Checkbox Limiter
 * Description: Limits checkbox selections in Forminator forms
 */
if (!defined('ABSPATH')) exit;

add_action('wp_footer', function() {
    ?>
    <script type="text/javascript">
    (function($) {
        const ForminatorLimiter = {
            config: {
                form_ids: [791, 9034, 64], // REPLACE WITH YOUR FORM IDs
                individual_limits: {
                    'checkbox-1': 1,
                    'checkbox-3': 4,
                    'checkbox-5': 7
                },
                group_limits: [
                    {
                        ids: ['checkbox-13', 'checkbox-14', 'checkbox-15', 'checkbox-16', 'checkbox-17', 'checkbox-18'],
                        limit: 7
                    }
                ]
            },

            init() {
                this.initForms();
                this.setupObservers();
                this.bindEvents();
            },

            initForms() {
                this.config.form_ids.forEach(formId => {
                    const $form = $(`#forminator-module-${formId}`);
                    if ($form.length) {
                        this.processIndividualLimits($form);
                        this.processGroupLimits($form);
                    }
                });
            },

            processIndividualLimits($form) {
                $form.find('.wpmudev-option-limit').each((i, field) => {
                    const $field = $(field);
                    const fieldId = $field.attr('id');
                    const limit = this.config.individual_limits[fieldId];
                    if (typeof limit !== 'undefined') {
                        this.setupIndividualField($field, limit);
                    }
                });
            },

            setupIndividualField($field, limit) {
                $field.find(':checkbox').on('change', (e) => {
                    this.handleIndividualCheckboxChange($field, limit);
                });
                this.updateIndividualFieldState($field, limit);
            },

            handleIndividualCheckboxChange($field, limit) {
                const checked = $field.find(':checked').length;
                if (checked >= limit) {
                    this.disableUnchecked($field);
                } else {
                    this.enableAll($field);
                }
            },

            updateIndividualFieldState($field, limit) {
                const checked = $field.find(':checked').length;
                if (checked >= limit) this.disableUnchecked($field);
            },

            processGroupLimits($form) {
                this.config.group_limits.forEach(group => {
                    const selectors = group.ids.map(id => `#${id}`).join(', ');
                    const $groups = $form.find(selectors);
                    if ($groups.length) {
                        this.setupGroup($groups, group.limit);
                    }
                });
            },

            setupGroup($groups, limit) {
                const $checkboxes = $groups.find(':checkbox');
                $checkboxes.on('change', () => this.handleGroupChange($groups, limit));
                this.updateGroupState($groups, limit);
            },

            handleGroupChange($groups, limit) {
                const checkedCount = $groups.find(':checked').length;
                if (checkedCount >= limit) {
                    this.disableUncheckedInGroup($groups);
                } else {
                    this.enableAllInGroup($groups);
                }
            },

            disableUncheckedInGroup($groups) {
                $groups.find(':checkbox:not(:checked)')
                    .prop('disabled', true)
                    .closest('.forminator-checkbox')
                    .addClass('wpmudev-disabled');
            },

            enableAllInGroup($groups) {
                $groups.find(':checkbox:disabled')
                    .prop('disabled', false)
                    .closest('.forminator-checkbox')
                    .removeClass('wpmudev-disabled');
            },

            updateGroupState($groups, limit) {
                const checkedCount = $groups.find(':checked').length;
                if (checkedCount >= limit) {
                    this.disableUncheckedInGroup($groups);
                }
            },

            disableUnchecked($field) {
                $field.find(':checkbox:not(:checked)')
                    .prop('disabled', true)
                    .closest('.forminator-checkbox')
                    .addClass('wpmudev-disabled');
            },

            enableAll($field) {
                $field.find(':checkbox:disabled')
                    .prop('disabled', false)
                    .closest('.forminator-checkbox')
                    .removeClass('wpmudev-disabled');
            },

            setupObservers() {
                if (typeof MutationObserver === 'undefined') return;
                
                new MutationObserver(mutations => {
                    mutations.forEach(() => this.initForms());
                }).observe(document.body, {
                    childList: true,
                    subtree: true
                });
            },

            bindEvents() {
                $(document).on('response.success.load.forminator', () => {
                    setTimeout(() => this.initForms(), 100);
                });
            }
        };

        $(() => ForminatorLimiter.init());
    })(jQuery);
    </script>
    <?php
}, 9999);