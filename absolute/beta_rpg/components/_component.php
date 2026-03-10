<?php
    /**
     * Renders a component from the components directory with the given name and props.
     *
     * @param string $name The name of the component (without .php extension).
     * @param array $props An associative array of props to pass to the component.
     *
     * @return void|string The component's stylesheet URLs if defined, otherwise void.
     */
    function component(string $name, array $props = []): ?string {
        extract($props);

        require __DIR__ . "/{$name}.php";

        if ( file_exists($_SERVER['DOCUMENT_ROOT'] . "/themes/components/{$name}.css") ) {
            return "/themes/components/{$name}.css";
        }

        return null;
    }
