{{-- 
    Modern Loading Modal Component
    Reusable loading modal with different states and animations
--}}

<script>
    /**
     * Modern Loading Modal System
     * Provides a consistent, modern loading experience across the application
     */
    
    class LoadingModal {
        constructor() {
            this.modal = null;
            this.currentState = null;
        }
        
        /**
         * Show loading modal with custom message and type
         * @param {string} message - Loading message (e.g., "Loading...", "Saving...", "Deleting...")
         * @param {string} type - Type of operation: 'loading', 'saving', 'deleting', 'processing'
         * @param {object} options - Additional options (icon, animation, etc.)
         */
        show(message = 'Loading...', type = 'loading', options = {}) {
            // Close existing modal if any
            this.hide();
            
            // Create modal element - consistent with other modals (bg-black/50, no blur)
            this.modal = document.createElement('div');
            this.modal.id = 'modern-loading-modal';
            this.modal.className = 'fixed inset-0 bg-black/50 z-[10001] flex items-center justify-center transition-opacity duration-300';
            this.modal.setAttribute('aria-live', 'polite');
            this.modal.setAttribute('aria-busy', 'true');
            
            // Get type-specific configuration
            const config = this.getTypeConfig(type);
            
            // Build modal content
            this.modal.innerHTML = `
                <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-sm w-full mx-4 transform transition-all duration-300 scale-95 opacity-0">
                    <div class="flex flex-col items-center justify-center space-y-4">
                        ${this.getSpinnerHTML(config)}
                        <div class="text-center space-y-2">
                            <h3 class="text-lg font-semibold text-gray-900">${message}</h3>
                            ${options.subMessage ? `<p class="text-sm text-gray-500">${options.subMessage}</p>` : ''}
                        </div>
                        ${this.getProgressBarHTML(options.showProgress)}
                    </div>
                </div>
            `;
            
            document.body.appendChild(this.modal);
            document.body.style.overflow = 'hidden';
            
            // Animate in
            setTimeout(() => {
                const content = this.modal.querySelector('.bg-white');
                if (content) {
                    content.classList.remove('scale-95', 'opacity-0');
                    content.classList.add('scale-100', 'opacity-100');
                }
                this.modal.classList.remove('opacity-0');
                this.modal.classList.add('opacity-100');
            }, 10);
            
            this.currentState = { message, type, options };
            return this.modal;
        }
        
        /**
         * Get type-specific configuration
         */
        getTypeConfig(type) {
            const configs = {
                loading: {
                    color: 'text-[#123A7D]',
                    bgColor: 'bg-[#123A7D]/10',
                    animation: 'spin'
                },
                saving: {
                    color: 'text-blue-600',
                    bgColor: 'bg-blue-50',
                    animation: 'spin'
                },
                deleting: {
                    color: 'text-red-600',
                    bgColor: 'bg-red-50',
                    animation: 'spin'
                },
                processing: {
                    color: 'text-indigo-600',
                    bgColor: 'bg-indigo-50',
                    animation: 'pulse'
                }
            };
            
            return configs[type] || configs.loading;
        }
        
        /**
         * Get spinner HTML based on type
         */
        getSpinnerHTML(config) {
            if (config.animation === 'pulse') {
                return `
                    <div class="relative">
                        <div class="w-16 h-16 ${config.bgColor} rounded-full flex items-center justify-center ${config.animation}">
                            <svg class="w-8 h-8 ${config.color}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </div>
                        <div class="absolute inset-0 ${config.bgColor} rounded-full animate-ping opacity-75"></div>
                    </div>
                `;
            }
            
            return `
                <div class="relative">
                    <div class="w-16 h-16 ${config.bgColor} rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 ${config.color} animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <div class="absolute inset-0 ${config.bgColor} rounded-full animate-ping opacity-20"></div>
                </div>
            `;
        }
        
        /**
         * Get progress bar HTML (optional)
         */
        getProgressBarHTML(showProgress) {
            if (!showProgress) return '';
            
            return `
                <div class="w-full max-w-xs mt-4">
                    <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-[#123A7D] to-[#10316A] rounded-full animate-progress"></div>
                    </div>
                </div>
            `;
        }
        
        /**
         * Update loading message
         */
        updateMessage(message, subMessage = null) {
            if (!this.modal) return;
            
            const messageEl = this.modal.querySelector('h3');
            const subMessageEl = this.modal.querySelector('p.text-sm');
            
            if (messageEl) {
                messageEl.textContent = message;
            }
            
            if (subMessage) {
                if (!subMessageEl) {
                    const subEl = document.createElement('p');
                    subEl.className = 'text-sm text-gray-500';
                    subEl.textContent = subMessage;
                    messageEl.parentElement.appendChild(subEl);
                } else {
                    subMessageEl.textContent = subMessage;
                }
            }
        }
        
        /**
         * Hide loading modal
         */
        hide() {
            if (this.modal) {
                const content = this.modal.querySelector('.bg-white');
                if (content) {
                    content.classList.remove('scale-100', 'opacity-100');
                    content.classList.add('scale-95', 'opacity-0');
                }
                this.modal.classList.remove('opacity-100');
                this.modal.classList.add('opacity-0');
                
                setTimeout(() => {
                    if (this.modal && this.modal.parentNode) {
                        this.modal.remove();
                    }
                    document.body.style.overflow = '';
                    this.modal = null;
                    this.currentState = null;
                }, 300);
            }
        }
    }
    
    // Create global instance
    const loadingModal = new LoadingModal();
    
    /**
     * Global helper functions for easy access
     */
    window.showLoadingModal = function(message = 'Loading...', type = 'loading', options = {}) {
        return loadingModal.show(message, type, options);
    };
    
    window.updateLoadingMessage = function(message, subMessage = null) {
        loadingModal.updateMessage(message, subMessage);
    };
    
    window.hideLoadingModal = function() {
        loadingModal.hide();
    };
    
    /**
     * Convenience functions for common operations
     */
    window.showLoading = function(message = 'Loading...') {
        return loadingModal.show(message, 'loading');
    };
    
    window.showSaving = function(message = 'Saving...') {
        return loadingModal.show(message, 'saving');
    };
    
    window.showDeleting = function(message = 'Deleting...') {
        return loadingModal.show(message, 'deleting');
    };
    
    window.showProcessing = function(message = 'Processing...') {
        return loadingModal.show(message, 'processing');
    };
</script>

<style>
    @keyframes progress {
        0% {
            transform: translateX(-100%);
        }
        100% {
            transform: translateX(100%);
        }
    }
    
    .animate-progress {
        animation: progress 1.5s ease-in-out infinite;
    }
</style>

