# Contributing To Absolute
Thanks for your interest in contributing to Absolute! :tada: We love getting [pull requests](https://www.quora.com/GitHub-What-is-a-pull-request) for bugfixes and contributions of our community to keep Absolute growing.

We want to keep it as easy as possible to contribute changes. These guidelines are intended to help smooth that process and allow us to review and approve your changes quickly and easily. Improvements are always welcome!

In general, we welcome pull requests that fix bugs.

For feature additions and large projects, please discuss with us at our [Discord](https://discord.gg/SHnvbsS) server first.

We'd hate to have to reject a pull request that you spent a long time working on.

Feel free to [open an issue](https://github.com/Toxocious/Absolute/issues/new) or [submit a new pull request](https://github.com/Toxocious/Absolute/compare). And finally, these are just guidelines, not rules, so use your best judgement when necessary.

If you're looking for inspiration for something to do, feel free to check out any open issues. We try to tag bug fixes or feature suggestions issues with [#good-first-issue](https://github.com/Toxocious/Absolute/issues?q=is%3Aissue+is%3Aopen+label%3A%22good+first+issue%22) when applicable, so check those ones out first if you're not yet accustomed to the code-base.

We only ask that you keep your code clean and easily readable.

We try to respond to pull requests within a few days, but feel free to bump yours or ping us over Discord if it seems like we forget about it. Sometimes we did, and sometimes there might be a miscommunication in terms of who is waiting for what.



## Reporting Bugs
Bugs should be reported on our [GitHub Issue Tracker](https://github.com/Toxocious/Absolute/issues/new) and should be correctly labeled as a `bug`, with additional labels where applicable.

Bugs that are reported through our Discord server will likely end up tracked on our repository as well.


## Requesting New Features
Feature requests should also be sent to our [GitHub Issue Tracker](https://github.com/Toxocious/Absolute/issues/new) and should be correctly labeled as a `feature`, with additional labels if necessary.

- Explain the problem that you're having, and anything you've tried to solve it using the currently available features.
- Explain how this new feature will help.
- If applicable, provide an example, like a code snippet, showing what your new feature might look like in use.


## Contributing a Fix or Feature
You've created a new fix or feature for Absolute. Awesome!

1. If you haven't already, create a fork of the Absolute repository.
2. Create a topic branch, and make all of your changes on that branch.
3. Submit a pull request, providing any essential details and information regarding your code and the issue/feature that you aim to resolve/implement.
4. Give me a moment. Absolute is currently maintained by a single person who does this on their limited free time, so it may take a bit to review your request.

If you're not sure what any of that means, check out Thinkful's [GitHub Pull Request Tutorial](https://github.com/Thinkful/guide-github-pull-request/blob/master/index.md) for a complete walkthrough of the process.


## Pull Requests
We expect any pull requests to be made from a new branch following the naming scheme of ``TYPE-BRANCH_NAME``.

Where `TYPE` is either `fix`, `feature`, or `refactor` and `BRANCH_NAME` is a brief descriptor of what the branch was made to accomplish.

Example branch names:
1. ``fix-broken_evolutions``
2. ``feature-christmas_event_2022``
3. ``refactor-battle_system``

Here's some steps on creating your own branch.

1. Fork the Project
2. Create your Feature Branch (``git checkout -b type-branch_name``)
3. Commit your Changes (``git commit -m 'commit description'``)
4. Push to the Branch (``git push origin type-branch_name``)
5. Open a Pull Request

### Writing a Good Pull Request
Some tips on writing a good pull request are as follows:

- Stay focused on a single fix or feature. If you submit multiple changes in a single request, we may like some but spot issues with others. When that happens, we have to reject the whole thing. If you submit each change in its own request it is easier for us to review and approve.
- Limit your changes to only what is required to implement the fix or feature. In particular, avoid style or formatting tools that may modify the formatting of other areas of the code.
- Use descriptive commit titles/messages. "feat: implemented \<feature\>" or "fix: \<problem\> is better than "Updated \<file\>".
- Make sure the code you submit compiles and runs without issues. When we set up unit tests and continuous integration we also expect that the pull request should pass all tests.
- Follow our coding conventions, which we've intentionally kept quite minimal.


## Code Standards
We generally prefer using the `Pascal_Snake_Case` naming convention for our code, this goes for all variables, functions, classes, and everything else.

When possible, opt for vanilla JavaScript over jQuery, as we're working on removing all jQuery from the game.

Any SQL queries should use prepared statements through PDO with SQL Injection in mind.

Use tabs for indentation, not spaces.

When in doubt, match the code that's already there.



## License
Your submitted code should be **MIT** licensed. The GitHub ToS (and the fact that your fork also contains our LICENSE file) ensures this, so we won't ask when you submit a pull request, but keep this in mind.

For simplicity, the first time you make a pull request, we'll ask you to explicitly state that you agree to **MIT** license it.
