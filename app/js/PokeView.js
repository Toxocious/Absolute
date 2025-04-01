const OpenPokemonIDs = [];

class FloatingWindow {
    constructor(pokemon_id, options = {}) {
        this.pokemon_id = pokemon_id;
        this.id = 'fw-' + Date.now() + '-' + Math.floor(Math.random() * 1000);
        this.title = 'Pokémon Summary';
        this.url = options.url || '';
        this.width = options.width || 700;
        this.height = options.height || 500;
        this.left = options.left || window.innerWidth / 2 - this.width / 2;
        this.top = options.top || window.innerHeight / 2 - this.height / 2;
        this.zIndex = 1000;
        this.element = null;
        this.isDragging = false;
        this.dragOffsetX = 0;
        this.dragOffsetY = 0;

        this.create();
        this.setupEvents();
    }

    create() {
        const container = document.createElement('div');
        container.id = this.id;
        container.className = 'floating-window border-gradient';
        container.style.left = `${this.left}px`;
        container.style.top = `${this.top}px`;

        const titleBar = document.createElement('div');
        titleBar.className = 'floating-window-title';

        const titleSpan = document.createElement('span');
        titleSpan.textContent = this.title;
        titleBar.appendChild(titleSpan);

        const closeButton = document.createElement('button');
        closeButton.innerHTML = '×';
        closeButton.onclick = () => this.close();
        titleBar.appendChild(closeButton);

        const iframeContainer = document.createElement('div');
        iframeContainer.className = 'floating-window-iframe';
        iframeContainer.style.flex = '1';
        iframeContainer.style.position = 'relative';

        const loadingIndicator = document.createElement('div');
        loadingIndicator.className = 'floating-window-loading';
        loadingIndicator.innerHTML = '<div class="loading-element"></div>';
        iframeContainer.appendChild(loadingIndicator);

        const iframe = document.createElement('iframe');
        iframe.src = this.url;
        iframe.onload = () => {
            iframe.style.opacity = '1';
            loadingIndicator.style.display = 'none';
        };
        iframeContainer.appendChild(iframe);

        container.appendChild(titleBar);
        container.appendChild(iframeContainer);

        document.body.appendChild(container);

        this.element = container;
        this.titleBar = titleBar;
        this.iframe = iframe;

        this.focus();
    }

    setupEvents() {
        this.titleBar.addEventListener('mousedown', (e) => {
            this.isDragging = true;
            this.dragOffsetX = e.clientX - this.element.offsetLeft;
            this.dragOffsetY = e.clientY - this.element.offsetTop;
            this.focus();
            e.preventDefault();
        });

        document.addEventListener('mousemove', (e) => {
            if (!this.isDragging) {
                return;
            }

            const left = e.clientX - this.dragOffsetX;
            const top = e.clientY - this.dragOffsetY;

            this.element.style.left = `${Math.max(
                0,
                Math.min(left, window.innerWidth - this.width)
            )}px`;
            this.element.style.top = `${Math.max(0, Math.min(top, window.innerHeight - 50))}px`;
        });

        document.addEventListener('mouseup', () => {
            this.isDragging = false;
        });

        this.element.addEventListener('mousedown', () => {
            this.focus();
        });
    }

    focus() {
        const windows = document.querySelectorAll('.floating-window');

        let baseZ = 1000;
        windows.forEach((win) => {
            if (win.id !== this.id) {
                win.style.zIndex = baseZ--;
            }
        });

        this.element.style.zIndex = 1001;
    }

    close() {
        if (this.element) {
            document.body.removeChild(this.element);
            this.element = null;

            const index = OpenPokemonIDs.indexOf(this.pokemon_id);
            if (index > -1) {
                OpenPokemonIDs.splice(index, 1);
            }
        }
    }
}

const PokemonViewer = {
    open: function (pokemon_id, options = {}) {
        if (OpenPokemonIDs.includes(pokemon_id)) {
            return;
        }

        OpenPokemonIDs.push(pokemon_id);

        return new FloatingWindow(pokemon_id, {
            url: `/core/ajax/pokemon.php?id=${pokemon_id}`,
            width: options.width || 700,
            height: options.height || 500,
            left: options.left,
            top: options.top,
        });
    },
};
