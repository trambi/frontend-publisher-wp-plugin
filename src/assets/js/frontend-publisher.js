/**
 * Frontend Article Publisher
 * Handle form actions
 */

(function($) {
    'use strict';

    let isSubmitting = false;

    const MAX_SIZE = 5_242_880; // 5 MiB
    const MAX_SIZE_STRING = '5 MiB';

    $(document).ready(function() {
        initializePublisher();
    });

    /**
     * Initialize
     */
    function initializePublisher() {
        bindFormSubmit();
        bindImagePreview();
        bindFormValidation();
        
        animateFormGroups();
    }

    /**
     * Handle form submit
     */
    function bindFormSubmit() {
        $('#frontend-publisher-form').on('submit', function(e) {
            e.preventDefault();
            
            if (isSubmitting) {
                return false;
            }
            
            const form = $(this);
            const submitBtn = $('#submit-article');
            const loading = $('#frontend-publisher-loading');
            const messageDiv = $('#frontend-publisher-message');

            // Fields validation
            if (!validateForm()) {
                showMessage('error', frontendPublisher.messages.required_fields);
                return false;
            }

            isSubmitting = true;
            setLoadingState(true);
            hideMessage();

            const formData = prepareFormData();

            // Send through AJAX
            submitArticle(formData)
                .done(function(response) {
                    handleSubmissionSuccess(response, form);
                })
                .fail(function(xhr, status, error) {
                    handleSubmissionError(xhr, status, error);
                })
                .always(function() {
                    setLoadingState(false);
                    isSubmitting = false;
                });
        });
    }

    /**
     * Validate the form before submit
     */
    function validateForm() {
        const title = $('#article_title').val().trim();
        let content = '';

        // Get the content of the rich editor
        if (typeof tinyMCE !== 'undefined' && tinyMCE.get('article_content')) {
            content = tinyMCE.get('article_content').getContent();
        } else {
            content = $('#article_content').val();
        }

        if (!title || !content.trim()) {
            if (!title) {
                $('#article_title').addClass('error-field');
            }
            if (!content.trim()) {
                const contentContainer = $('.wp-editor-container');
                if (contentContainer.length) {
                    contentContainer.addClass('error-field');
                } else {
                    $('#article_content').addClass('error-field');
                }
            }
            return false;
        }

        $('.error-field').removeClass('error-field');
        return true;
    }

    /**
     * Prepare form data to be sent
     */
    function prepareFormData() {
        const formData = new FormData();
        
        // Get the content of the rich editor
        let content = '';
        if (typeof tinyMCE !== 'undefined' && tinyMCE.get('article_content')) {
            content = tinyMCE.get('article_content').getContent();
        } else {
            content = $('#article_content').val();
        }

        formData.append('action', 'submit_frontend_article');
        formData.append('nonce', frontendPublisher.nonce);
        formData.append('title', $('#article_title').val().trim());
        formData.append('content', content);
        formData.append('excerpt', $('#article_excerpt').val());
        formData.append('categories', JSON.stringify($('#article_categories').val() || []));
        formData.append('tags', $('#article_tags').val());
        formData.append('status', $('#article_status').val());

        const imageFile = $('#article_featured_image')[0].files[0];
        if (imageFile) {
            formData.append('featured_image', imageFile);
        }

        return formData;
    }

    /**
     * Send article through AJAX
     */
    function submitArticle(formData) {
        return $.ajax({
            url: frontendPublisher.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            timeout: 30000, // 30 secondes
            cache: false
        });
    }

    /**
     * Handle submit success
     */
    function handleSubmissionSuccess(response, form) {
        if (response.success) {
            showMessage('success', response.data.message);
            resetForm(form);
            
            // Scroll vers le message de succès
            $('html, body').animate({
                scrollTop: $('#frontend-publisher-message').offset().top - 20
            }, 500);
            
        } else {
            showMessage('error', response.data.message || frontendPublisher.messages.error);
        }
    }

    /**
     * Handle submit error
     */
    function handleSubmissionError(xhr, status, error) {
        let errorMessage = frontendPublisher.messages.error;
        
        if (status === 'timeout') {
            errorMessage = 'Délai d\'attente dépassé. Veuillez réessayer.';
        } else if (xhr.status === 413) {
            errorMessage = 'Fichier trop volumineux. Veuillez choisir une image plus petite.';
        } else if (xhr.status === 0) {
            errorMessage = 'Problème de connexion. Vérifiez votre connexion internet.';
        }
        
        showMessage('error', errorMessage);
        console.error('Erreur AJAX:', status, error);
    }

    /**
     * Reset form after success
     */
    function resetForm(form) {
        form[0].reset();
        
        // Reset TinyMCE
        if (typeof tinyMCE !== 'undefined' && tinyMCE.get('article_content')) {
            tinyMCE.get('article_content').setContent('');
        }
        
        // Reset multiple selects
        $('#article_categories').val([]).trigger('change');
        
        $('#image-preview').remove();
    }

    /**
     * Display/hide loading state
     */
    function setLoadingState(loading) {
        const submitBtn = $('#submit-article');
        const loadingDiv = $('#frontend-publisher-loading');

        if (loading) {
            submitBtn.prop('disabled', true).text('Publication en cours...');
            loadingDiv.show();
        } else {
            submitBtn.prop('disabled', false).text('Publier l\'article');
            loadingDiv.hide();
        }
    }

    /**
     * Show the message
     */
    function showMessage(type, text) {
        const messageDiv = $('#frontend-publisher-message');
        messageDiv
            .removeClass('success error')
            .addClass(type)
            .text(text)
            .slideDown(300);
    }

    /**
     * Hide the message
     */
    function hideMessage() {
        $('#frontend-publisher-message').slideUp(200);
    }

    /**
     * Handle image preview
     */
    function bindImagePreview() {
        $('#article_featured_image').on('change', function(e) {
            const file = e.target.files[0];
            
            $('#image-preview').remove();
            
            if (file && file.type.startsWith('image/')) {
                // Check max size
                if (file.size > MAX_SIZE) {
                    showMessage('error', `L\'image est trop volumineuse (maximum ${formatFileSize(MAX_SIZE)}).`);
                    $(this).val('');
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = $('<div id="image-preview" style="margin-top: 10px;">' +
                        '<img src="' + e.target.result + '" style="max-width: 200px; max-height: 150px; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">' +
                        '<p style="font-size: 12px; color: #666; margin: 5px 0 0 0;">' + file.name + ' (' + formatFileSize(file.size) + ')</p>' +
                        '</div>');
                    
                    $('#article_featured_image').after(preview);
                };
                reader.readAsDataURL(file);
            }
        });
    }

    /**
     * Fields validation
     */
    function bindFormValidation() {
        $('#article_title').on('blur', function() {
            const $this = $(this);
            if ($this.val().trim() === '') {
                $this.addClass('error-field');
            } else {
                $this.removeClass('error-field');
            }
        });

        if (typeof tinyMCE !== 'undefined') {
            $(document).on('tinymce-editor-init', function(event, editor) {
                editor.on('blur', function() {
                    const content = editor.getContent();
                    const container = $('.wp-editor-container');
                    
                    if (!content.trim()) {
                        container.addClass('error-field');
                    } else {
                        container.removeClass('error-field');
                    }
                });
            });
        }

        $('#frontend-publisher-form input, #frontend-publisher-form textarea, #frontend-publisher-form select').on('input change', function() {
            $(this).removeClass('error-field');
            $('.wp-editor-container').removeClass('error-field');
        });
    }

    /**
     * Animater form groups
     */
    function animateFormGroups() {
        $('.form-group').each(function(index) {
            $(this).css({
                'opacity': '0',
                'transform': 'translateY(20px)'
            }).delay(index * 100).animate({
                'opacity': '1'
            }, 300, function() {
                $(this).css('transform', 'translateY(0px)');
            });
        });
    }

    /**
     * Get file size in human format
     */
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    /**
     * Enhance accessibility by adding aria properties
     */
    function enhanceAccessibility() {
        $('#frontend-publisher-form').attr('aria-label', 'Formulaire de publication d\'article');
        $('.form-group input, .form-group textarea, .form-group select').each(function() {
            const $this = $(this);
            const label = $this.closest('.form-group').find('label');
            if (label.length) {
                const labelId = label.attr('for') + '-label';
                label.attr('id', labelId);
                $this.attr('aria-labelledby', labelId);
            }
        });

        $('#frontend-publisher-message').attr('role', 'alert').attr('aria-live', 'polite');
    }

    $(document).ready(function() {
        enhanceAccessibility();
    });

})(jQuery);