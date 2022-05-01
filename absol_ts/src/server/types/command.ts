export interface CommandInterface {
  name: string;
  description: string;
  args?: any;
  execute: Function;
}
