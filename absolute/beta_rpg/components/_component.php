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

        $Stylesheet = null;

        if ( file_exists($_SERVER['DOCUMENT_ROOT'] . "/themes/components/{$name}.css") ) {
            $Stylesheet = "/themes/components/{$name}.css";
            Add_Component_Stylesheet($Stylesheet);
        }

        if ( file_exists($_SERVER['DOCUMENT_ROOT'] . "/js/components/{$name}.js") ) {
            $Script = "/js/components/{$name}.js";
            Add_Component_Script($Script);
        }

        return $Stylesheet;
    }

    function Add_Component_Stylesheet(?string $path): void {
        if (!$path) {
            return;
        }

        if (!isset($GLOBALS['Absolute_Beta_Component_Stylesheets'])) {
            $GLOBALS['Absolute_Beta_Component_Stylesheets'] = [];
        }

        if (!in_array($path, $GLOBALS['Absolute_Beta_Component_Stylesheets'], true)) {
            $GLOBALS['Absolute_Beta_Component_Stylesheets'][] = $path;
        }
    }

    function Add_Component_Script(?string $path): void {
        if (!$path) {
            return;
        }

        if (!isset($GLOBALS['Absolute_Beta_Component_Scripts'])) {
            $GLOBALS['Absolute_Beta_Component_Scripts'] = [];
        }

        if (!in_array($path, $GLOBALS['Absolute_Beta_Component_Scripts'], true)) {
            $GLOBALS['Absolute_Beta_Component_Scripts'][] = $path;
        }
    }

    function Get_Component_Stylesheets(): array {
        return $GLOBALS['Absolute_Beta_Component_Stylesheets'] ?? [];
    }

    function Get_Component_Scripts(): array {
        return $GLOBALS['Absolute_Beta_Component_Scripts'] ?? [];
    }
