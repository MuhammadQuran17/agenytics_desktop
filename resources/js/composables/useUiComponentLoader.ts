import { defineAsyncComponent, type Component } from 'vue';
import { camelCase, upperFirst } from 'lodash-es';

/**
 * UI Component Strategy Pattern
 * Dynamically loads components based on ui_type from the AI response
 * Uses automatic naming convention: snake_case ui_type -> PascalCase component name
 */

// Special case mappings for ui_types that don't follow naming convention
const specialCaseMappings: Record<string, string> = {
    // fill if needed
};

// Statically analyzable glob import so Vite can correctly chunk and name these
// components (a template-literal `import()` produces an ambiguous manifest entry).
const uiTypeModules = import.meta.glob<{ default: Component }>('../components_project/chat/ui_types/*.vue');

/**
 * Converts ui_type to component name using lodash
 * Example: 'line_chart' -> 'LineChart', 'database-schema' -> 'DatabaseSchema'
 */
const convertUiTypeToComponentName = (uiType: string): string => {
    // Check special case mappings first
    if (specialCaseMappings[uiType]) {
        return specialCaseMappings[uiType];
    }

    // Convert snake_case/kebab-case to camelCase, then to PascalCase
    return upperFirst(camelCase(uiType));
};

/**
 * Strategy function to load the appropriate component based on ui_type
 */
export const useUiComponentLoader = () => {
    const loadComponent = (uiType: string): Component | null => {
        if (!uiType) {
            console.warn('ui_type is required');
            return null;
        }

        const componentName = convertUiTypeToComponentName(uiType);
        const modulePath = `../components_project/chat/ui_types/${componentName}.vue`;
        const loader = uiTypeModules[modulePath];

        if (!loader) {
            console.error(`Failed to load component: ${componentName} (ui_type: ${uiType})`);
            return null;
        }

        return defineAsyncComponent(loader);
    };

    return {
        loadComponent,
    };
};
