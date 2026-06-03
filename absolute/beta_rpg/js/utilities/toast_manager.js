class ToastManager {
    constructor() {
        this.toasts = [];

        const ToastContainerElement = document.createElement('div');
        ToastContainerElement.className = 'toast-container';
        ToastContainerElement.id = 'toast-container';
        document.body.append(ToastContainerElement);
    }

    PushToast(toast) {
        this.toasts.push(toast);

        const element = document.createElement('div');
        element.className = `toast`;
        if (toast.type) {
            element.classList.add(`toast-${toast.type}`);
        }
        element.dataset.toastId = toast.toast_id;

        const title = document.createElement('div');
        title.className = 'toast-title';
        title.textContent = toast.toast_title;
        element.appendChild(title);

        const content = document.createElement('div');
        content.className = 'toast-content';
        content.textContent = toast.toast_text;

        const progress = document.createElement('div');
        progress.className = 'toast-progress';
        progress.style.animationDuration = `${toast.duration}ms`;

        let close = null;
        if (toast.manual) {
            close = document.createElement('button');
            close.className = 'toast-close';

            close.innerHTML = `
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x-icon lucide-x"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        `;

            close.addEventListener('click', () => {
                this.RemoveToast(toast.toast_id);
            });
        }

        if (toast.manual) {
            element.append(title, content, close);
        } else {
            element.append(title, content, progress);
        }

        document.getElementById('toast-container').appendChild(element);

        if (!toast.manual) {
            setTimeout(() => {
                this.RemoveToast(toast.toast_id);
            }, toast.duration);
        }
    }

    RemoveToast(toast_id) {
        this.toasts = this.toasts.filter((toast) => toast.toast_id !== toast_id);

        const element = document.querySelector(`[data-toast-id="${toast_id}"]`);

        if (!element) {
            return;
        }

        element.classList.add('toast-hiding');

        setTimeout(() => {
            element.remove();
        }, 200);
    }
}

class Toast {
    constructor(
        toast_title,
        toast_text,
        { type = undefined, toast_duration = 3000, manual = false } = {}
    ) {
        this.toast_id = crypto.randomUUID();
        this.toast_text = toast_text;
        this.toast_title = toast_title;

        this.type = type;
        this.manual = manual;
        this.duration = toast_duration;

        this.created = false;
        this.created_at = Date.now();
    }
}

const ToastManagerImpl = new ToastManager();

// Example usage: SpawnToast('This is a success message!', 'success');
function SpawnToast(title, text, type = null, duration = 3000, manual = false) {
    const NewToast = new Toast(title, text, {
        type: type,
        toast_duration: duration,
        manual: manual,
    });
    ToastManagerImpl.PushToast(NewToast);
}
