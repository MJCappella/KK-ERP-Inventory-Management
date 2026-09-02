/**
 * Sonner Toast Library
 */
(function (root, factory) {
    if (typeof define === 'function' && define.amd) {
        define([], factory);
    } else if (typeof module === 'object' && module.exports) {
        module.exports = factory();
    } else {
        root.toast = factory();
    }
}(typeof self !== 'undefined' ? self : this, function () {
    let container = null;
    let toastGroup = null;
    let toasts = [];
    let isHovered = false;

    const ICONS = {
        success: `<i class="fa-solid fa-circle-check sonner-icon"></i>`,
        error: `<i class="fa-solid fa-circle-xmark sonner-icon"></i>`,
        warning: `<i class="fa-solid fa-triangle-exclamation sonner-icon"></i>`,
        info: `<i class="fa-solid fa-circle-info sonner-icon"></i>`,
    };

    function initDOM() {
        if (container) return;

        container = document.createElement('section');
        container.className = 'sonner-toaster';
        container.setAttribute('aria-label', 'Notifications');

        toastGroup = document.createElement('ol');
        toastGroup.className = 'sonner-toast-group';

        container.appendChild(toastGroup);
        document.body.appendChild(container);

        container.addEventListener('mouseenter', () => {
            isHovered = true;
            toasts.forEach(t => t.pauseTimer());
        });

        container.addEventListener('mouseleave', () => {
            isHovered = false;
            toasts.forEach(t => t.resumeTimer());
        });
    }

    function updateLayout() {
        if (!toastGroup) return;

        let totalOffset = 0;
        toasts.forEach((item, index) => {
            item.el.setAttribute('data-index', index);
            item.el.style.setProperty('--expanded-offset', `${totalOffset}px`);
            item.el.style.zIndex = Math.max(1, 100 - index);

            // Add height + gap (12px) for expanded stacking offset
            const h = item.el.getBoundingClientRect().height || 56;
            totalOffset += h + 10;
        });
    }

    function createToast(title, options = {}, type = 'default') {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => createToast(title, options, type));
            return;
        }

        initDOM();

        const id = 'sonner_' + Math.random().toString(36).substr(2, 9);
        const duration = options.duration !== undefined ? options.duration : 4000;
        const description = options.description || '';

        const li = document.createElement('li');
        li.className = 'sonner-toast';
        li.id = id;
        li.setAttribute('data-type', type);
        li.setAttribute('data-state', 'entering');

        const iconHtml = ICONS[type] || '';

        li.innerHTML = `
            ${iconHtml}
            <div class="sonner-content">
                <div class="sonner-title">${title}</div>
                ${description ? `<div class="sonner-description">${description}</div>` : ''}
            </div>
            <button class="sonner-close-btn" aria-label="Close notification"><i class="fa-solid fa-xmark text-[11px]"></i></button>
        `;

        const closeBtn = li.querySelector('.sonner-close-btn');
        closeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            dismiss(id);
        });

        toastGroup.prepend(li);

        let remainingTime = duration;
        let startTime = Date.now();
        let timerId = null;

        const toastRecord = {
            id,
            el: li,
            pauseTimer: () => {
                if (timerId) {
                    clearTimeout(timerId);
                    timerId = null;
                    remainingTime -= (Date.now() - startTime);
                }
            },
            resumeTimer: () => {
                if (duration > 0 && remainingTime > 0 && !timerId) {
                    startTime = Date.now();
                    timerId = setTimeout(() => dismiss(id), remainingTime);
                }
            }
        };

        toasts.unshift(toastRecord);

        requestAnimationFrame(() => {
            li.removeAttribute('data-state');
            updateLayout();
        });

        if (duration > 0 && !isHovered) {
            toastRecord.resumeTimer();
        }

        if (toasts.length > 5) {
            const oldest = toasts[toasts.length - 1];
            dismiss(oldest.id);
        }

        return id;
    }

    function dismiss(id) {
        const index = toasts.findIndex(t => t.id === id);
        if (index === -1) return;

        const [toastItem] = toasts.splice(index, 1);
        toastItem.pauseTimer();
        toastItem.el.setAttribute('data-state', 'exiting');

        setTimeout(() => {
            if (toastItem.el.parentNode) {
                toastItem.el.parentNode.removeChild(toastItem.el);
            }
            updateLayout();
        }, 300);

        updateLayout();
    }

    const toast = function (title, options) {
        return createToast(title, options, 'default');
    };

    toast.success = (title, options) => createToast(title, options, 'success');
    toast.error = (title, options) => createToast(title, options, 'error');
    toast.warning = (title, options) => createToast(title, options, 'warning');
    toast.info = (title, options) => createToast(title, options, 'info');
    toast.dismiss = dismiss;

    // Automatic hook for Laravel Blade flashed sessions
    document.addEventListener('DOMContentLoaded', () => {
        initDOM();
    });

    return toast;
}));
