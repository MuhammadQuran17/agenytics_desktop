declare module 'simple-code-editor' {
  import { DefineComponent } from 'vue';

  const component: DefineComponent<{
    value?: string;
    readonly?: boolean;
    theme?: string;
    width?: string;
    height?: string;
    languages?: string[][];
  }>;

  export default component;
}
