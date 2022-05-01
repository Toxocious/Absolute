export = {
  name: 'test',
  description: 'Generic test command.',
  args: false,

  execute: () => {
    return {
      message: 'Test command executed.',
    };
  },
};
