/**
 * Hyro Alert System
 * Beautiful, customizable alerts for the Hyro admin panel
 */

class HyroAlert {
    constructor() {
        this.container = null;
        this.activeAlert = null;
        this.init();
    }

    init() {
        // Create container if it doesn't exist
        if (!document.getElementById('hyro-alert-container')) {
            this.container = document.createElement('div');
            this.container.id = 'hyro-alert-container';
            this.container.className = 'hyro-alert-container';
            document.body.appendChild(this.container);
        } else {
            this.container = document.getElementById('hyro-alert-container');
        }
    }

    /**
     * Show success alert
     */
    success(title, message = '', options = {}) {
        return this.show({
            type: 'success',
            title,
            message,
            icon: this.getIcon('success'),
            ...options
        });
    }

    /**
     * Show error alert
     */
    error(title, message = '', options = {}) {
        return this.show({
            type: 'error',
            title,
            message,
            icon: this.getIcon('error'),
            ...options
        });
    }

    /**
     * Show warning alert
     */
    warning(title, message = '', options = {}) {
        return this.show({
            type: 'warning',
            title,
            message,
            icon: this.getIcon('warning'),
            ...options
        });
    }

    /**
     * Show info alert
     */
    info(title, message = '', options = {}) {
        return this.show({
            type: 'info',
            title,
            message,
            icon: this.getIcon('info'),
            ...options
        });
    }

    /**
     * Show confirmation dialog
     */
    confirm(title, message = '', options = {}) {
        return new Promise((resolve) => {
            this.show({
                type: 'warning',
                title,
                message,
                icon: this.getIcon('question'),
                showCancelButton: true,
                confirmButtonText: options.confirmButtonText || 'Yes, confirm',
                cancelButtonText: options.cancelButtonText || 'Cancel',
                ...options,
                onConfirm: () => resolve(true),
                onCancel: () => resolve(false)
            });
        });
    }

    /**
     * Show input dialog
     */
    input(title, options = {}) {
        return new Promise((resolve, reject) => {
            this.show({
                type: 'info',
                title,
                message: options.message || '',
                icon: this.getIcon('question'),
                showInput: true,
                inputType: options.inputType || 'text',
                inputPlaceholder: options.inputPlaceholder || '',
                inputValue: options.inputValue || '',
                showCancelButton: true,
                confirmButtonText: options.confirmButtonText || 'Submit',
                cancelButtonText: options.cancelButtonText || 'Cancel',
                ...options,
                onConfirm: (value) => {
                    if (!value && options.required) {
                        this.error('Required', 'Please enter a value');
                        reject('Input is required');
                    } else {
                        resolve(value);
                    }
                },
                onCancel: () => reject('cancelled')
            });
        });
    }

    /**
     * Show choice dialog (select from options)
     */
    choice(title, choices = [], options = {}) {
        return new Promise((resolve, reject) => {
            this.show({
                type: 'info',
                title,
                message: options.message || '',
                icon: this.getIcon('question'),
                showChoice: true,
                choices,
                showCancelButton: true,
                confirmButtonText: options.confirmButtonText || 'Select',
                cancelButtonText: options.cancelButtonText || 'Cancel',
                ...options,
                onConfirm: (value) => {
                    if (!value && options.required) {
                        this.error('Required', 'Please select an option');
                        reject('Selection is required');
                    } else {
                        resolve(value);
                    }
                },
                onCancel: () => reject('cancelled')
            });
        });
    }

    /**
     * Show toast notification
     */
    toast(message, type = 'info', duration = 3000) {
        const toast = document.createElement('div');
        toast.className = `hyro-toast hyro-toast-${type}`;
        toast.innerHTML = `
            <div class="hyro-toast-icon">${this.getIcon(type)}</div>
            <div class="hyro-toast-message">${message}</div>
        `;

        const toastContainer = this.getToastContainer();
        toastContainer.appendChild(toast);

        // Animate in
        setTimeout(() => toast.classList.add('show'), 10);

        // Auto remove
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }

    /**
     * Main show method
     */
    show(config) {
        // Close existing alert
        if (this.activeAlert) {
            this.close();
        }

        const alert = this.createAlert(config);
        this.container.appendChild(alert);
        this.activeAlert = alert;

        // Animate in
        setTimeout(() => {
            alert.classList.add('show');
        }, 10);

        // Auto close if specified
        if (config.timer) {
            setTimeout(() => this.close(), config.timer);
        }

        return this;
    }

    /**
     * Create alert element
     */
    createAlert(config) {
        const overlay = document.createElement('div');
        overlay.className = 'hyro-alert-overlay';

        const alert = document.createElement('div');
        alert.className = `hyro-alert hyro-alert-${config.type}`;

        // Icon
        if (config.icon) {
            const iconDiv = document.createElement('div');
            iconDiv.className = 'hyro-alert-icon';
            iconDiv.innerHTML = config.icon;
            alert.appendChild(iconDiv);
        }

        // Title
        if (config.title) {
            const title = document.createElement('h3');
            title.className = 'hyro-alert-title';
            title.textContent = config.title;
            alert.appendChild(title);
        }

        // Message
        if (config.message) {
            const message = document.createElement('p');
            message.className = 'hyro-alert-message';
            message.innerHTML = config.message;
            alert.appendChild(message);
        }

        // Input field
        if (config.showInput) {
            const inputGroup = document.createElement('div');
            inputGroup.className = 'hyro-alert-input-group';
            
            const input = document.createElement('input');
            input.type = config.inputType || 'text';
            input.className = 'hyro-alert-input';
            input.placeholder = config.inputPlaceholder || '';
            input.value = config.inputValue || '';
            
            inputGroup.appendChild(input);
            alert.appendChild(inputGroup);
        }

        // Choice select
        if (config.showChoice && config.choices) {
            const selectGroup = document.createElement('div');
            selectGroup.className = 'hyro-alert-select-group';
            
            const select = document.createElement('select');
            select.className = 'hyro-alert-select';
            
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = '-- Select an option --';
            select.appendChild(defaultOption);
            
            config.choices.forEach(choice => {
                const option = document.createElement('option');
                option.value = typeof choice === 'object' ? choice.value : choice;
                option.textContent = typeof choice === 'object' ? choice.label : choice;
                select.appendChild(option);
            });
            
            selectGroup.appendChild(select);
            alert.appendChild(selectGroup);
        }

        // Buttons
        const buttons = document.createElement('div');
        buttons.className = 'hyro-alert-buttons';

        if (config.showCancelButton) {
            const cancelBtn = document.createElement('button');
            cancelBtn.className = 'hyro-alert-btn hyro-alert-btn-cancel';
            cancelBtn.textContent = config.cancelButtonText || 'Cancel';
            cancelBtn.onclick = () => {
                this.close();
                if (config.onCancel) config.onCancel();
            };
            buttons.appendChild(cancelBtn);
        }

        const confirmBtn = document.createElement('button');
        confirmBtn.className = 'hyro-alert-btn hyro-alert-btn-confirm';
        confirmBtn.textContent = config.confirmButtonText || 'OK';
        confirmBtn.onclick = () => {
            let value = null;
            
            if (config.showInput) {
                value = alert.querySelector('.hyro-alert-input').value;
            } else if (config.showChoice) {
                value = alert.querySelector('.hyro-alert-select').value;
            }
            
            this.close();
            if (config.onConfirm) config.onConfirm(value);
        };
        buttons.appendChild(confirmBtn);

        alert.appendChild(buttons);
        overlay.appendChild(alert);

        // Close on overlay click
        if (config.closeOnClickOutside !== false) {
            overlay.onclick = (e) => {
                if (e.target === overlay) {
                    this.close();
                    if (config.onCancel) config.onCancel();
                }
            };
        }

        return overlay;
    }

    /**
     * Close active alert
     */
    close() {
        if (this.activeAlert) {
            this.activeAlert.classList.remove('show');
            setTimeout(() => {
                if (this.activeAlert) {
                    this.activeAlert.remove();
                    this.activeAlert = null;
                }
            }, 300);
        }
    }

    /**
     * Get toast container
     */
    getToastContainer() {
        let container = document.getElementById('hyro-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'hyro-toast-container';
            container.className = 'hyro-toast-container';
            document.body.appendChild(container);
        }
        return container;
    }

    /**
     * Get icon SVG
     */
    getIcon(type) {
        const icons = {
            success: `<svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>`,
            error: `<svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>`,
            warning: `<svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>`,
            info: `<svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>`,
            question: `<svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>`
        };
        return icons[type] || icons.info;
    }
}

// Initialize global instance
window.HyroAlert = new HyroAlert();

// Convenience methods
window.hyroAlert = {
    success: (title, message, options) => window.HyroAlert.success(title, message, options),
    error: (title, message, options) => window.HyroAlert.error(title, message, options),
    warning: (title, message, options) => window.HyroAlert.warning(title, message, options),
    info: (title, message, options) => window.HyroAlert.info(title, message, options),
    confirm: (title, message, options) => window.HyroAlert.confirm(title, message, options),
    input: (title, options) => window.HyroAlert.input(title, options),
    choice: (title, choices, options) => window.HyroAlert.choice(title, choices, options),
    toast: (message, type, duration) => window.HyroAlert.toast(message, type, duration)
};
