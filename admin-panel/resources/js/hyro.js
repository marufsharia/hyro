// Hyro Package JavaScript - Alpine.js 3 & Livewire 3
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';
import intersect from '@alpinejs/intersect';

// Register Alpine plugins
Alpine.plugin(collapse);
Alpine.plugin(focus);
Alpine.plugin(intersect);

// Make Alpine available globally for Livewire
window.Alpine = Alpine;

// ============================================
// Alpine.js Data Components
// ============================================

// Sidebar Component
Alpine.data('sidebar', () => ({
    open: localStorage.getItem('sidebar-open') === 'true',
    
    toggle() {
        this.open = !this.open;
        localStorage.setItem('sidebar-open', this.open);
    }
}));

// Dropdown Component
Alpine.data('dropdown', () => ({
    open: false,
    
    toggle() {
        this.open = !this.open;
    },
    
    close() {
        this.open = false;
    }
}));

// Tabs Component
Alpine.data('tabs', (defaultTab = 0) => ({
    activeTab: defaultTab,
    
    setTab(index) {
        this.activeTab = index;
    }
}));

// Toast Notification Component
Alpine.data('toast', () => ({
    show: false,
    message: '',
    type: 'info',
    
    showToast(message, type = 'info') {
        this.message = message;
        this.type = type;
        this.show = true;
        
        setTimeout(() => {
            this.show = false;
        }, 5000);
    }
}));

// Confirm Dialog Component
Alpine.data('confirmDialog', () => ({
    show: false,
    message: '',
    onConfirm: null,
    
    open(message, callback) {
        this.message = message;
        this.onConfirm = callback;
        this.show = true;
    },
    
    confirm() {
        if (this.onConfirm) {
            this.onConfirm();
        }
        this.show = false;
    },
    
    cancel() {
        this.show = false;
    }
}));

// File Upload Component
Alpine.data('fileUpload', () => ({
    uploading: false,
    progress: 0
}));

// Table Sort Component
Alpine.data('tableSort', (defaultColumn = null, defaultDirection = 'asc') => ({
    sortColumn: defaultColumn,
    sortDirection: defaultDirection,
    
    sort(column) {
        if (this.sortColumn === column) {
            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            this.sortColumn = column;
            this.sortDirection = 'asc';
        }
    }
}));

// ============================================
// Alpine Magic Properties
// ============================================

// $clipboard magic for copying text
Alpine.magic('clipboard', () => {
    return (text) => {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: { message: 'Copied to clipboard!', type: 'success' }
                }));
            });
        }
    };
});

// ============================================
// Alpine Directives
// ============================================

// x-tooltip directive
Alpine.directive('tooltip', (el, { expression }, { evaluate }) => {
    const text = evaluate(expression);
    
    el.addEventListener('mouseenter', () => {
        const tooltip = document.createElement('div');
        tooltip.className = 'fixed z-50 px-3 py-2 text-xs text-white bg-gray-900 rounded-lg shadow-lg pointer-events-none';
        tooltip.textContent = text;
        tooltip.style.opacity = '0';
        tooltip.style.transition = 'opacity 0.2s';
        
        document.body.appendChild(tooltip);
        
        const rect = el.getBoundingClientRect();
        tooltip.style.left = `${rect.left + rect.width / 2}px`;
        tooltip.style.top = `${rect.top - 10}px`;
        tooltip.style.transform = 'translateX(-50%) translateY(-100%)';
        
        setTimeout(() => tooltip.style.opacity = '1', 10);
        
        el._tooltip = tooltip;
    });
    
    el.addEventListener('mouseleave', () => {
        if (el._tooltip) {
            el._tooltip.style.opacity = '0';
            setTimeout(() => {
                if (el._tooltip && el._tooltip.parentNode) {
                    el._tooltip.parentNode.removeChild(el._tooltip);
                    delete el._tooltip;
                }
            }, 200);
        }
    });
});

// ============================================
// Global Event Listeners
// ============================================

document.addEventListener('alpine:init', () => {
     // Make $wire available globally for Alpine
    Alpine.magic('wire', (el) => {
        if (window.livewire) {
            return window.livewire.find(el.closest('[wire\\:id]').getAttribute('wire:id'));
        }
        return null;
    });
    // Listen for Livewire upload events
    window.addEventListener('livewire-upload-start', () => {
        console.log('Upload started');
    });
    
    window.addEventListener('livewire-upload-finish', () => {
        console.log('Upload finished');
    });
    
    window.addEventListener('livewire-upload-error', () => {
        window.dispatchEvent(new CustomEvent('notify', {
            detail: { message: 'Upload failed', type: 'error' }
        }));
    });
});

// ============================================
// Utility Functions
// ============================================

class HyroUtils {
    static getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    }
    
    static async fetchWithTimeout(resource, options = {}) {
        const { timeout = 8000 } = options;
        const controller = new AbortController();
        const id = setTimeout(() => controller.abort(), timeout);
        
        const response = await fetch(resource, {
            ...options,
            signal: controller.signal,
        });
        
        clearTimeout(id);
        return response;
    }
    
    static debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}

// Make utilities available globally
window.HyroUtils = HyroUtils;

// DON'T start Alpine here - let Livewire handle it
// Livewire will automatically start Alpine when it initializes
// Alpine.start(); // REMOVED - causes duplicate Alpine instances

// Export for ES module usage
export default Alpine;
