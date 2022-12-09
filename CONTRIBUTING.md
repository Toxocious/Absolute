# Contributing
In general, we welcome pull requests that fix bugs.

For feature additions and large projects, please discuss with us at our [Discord](https://discord.gg/SHnvbsS) server first.
We'd hate to have to reject a pull request that you spent a long time working on.

If you're looking for inspiration for something to do, feel free to check out any open issues. We try to tag appropriate issues with #good-first-issue when applicable, so check those ones out first if you're not yet accustomed to the code-base.

We only ask that you keep your code clean and easily readable.

We try to respond to pull requests within a few days, but feel free to bump yours if it seems like we forget about it. Sometimes we did, and sometimes there might be a miscommunication in terms of who is waiting for what.

## Pull Requests
We expect any pull requests to be made from a new branch following the naming scheme of ``TYPE-BRANCH_NAME``.

Where `TYPE` is either `fix`, `feature`, or `refactor` and `BRANCH_NAME` is a brief descriptor of what the branch was made to accomplish.

Example branch names:
1. ``fix-broken_evolutions``
2. ``feature-christmas_event_2022``
3. ``refactor-battle_system``

Here's some steps on creating your own branch.

4. Fork the Project
5. Create your Feature Branch (``git checkout -b type-branch_name``)
6. Commit your Changes (``git commit -m 'commit description'``)
7. Push to the Branch (``git push origin type-branch_name``)
8. Open a Pull Request



### Code Standards
We generally prefer using the `Pascal_Snake_Case` naming convention for our code, this goes for all variables, functions, classes, and everything else.

When possible, opt for vanilla JavaScript over jQuery, as we're working on removing all jQuery from the game.

Any SQL queries should use prepared statements through PDO with SQL Injection in mind.



# License
Your submitted code should be **GNU GPL 3** licensed. The GitHub ToS (and the fact that your fork also contains our LICENSE file) ensures this, so we won't ask when you submit a pull request, but keep this in mind.

For simplicity, the first time you make a client pull request, we'll ask you to explicitly state that you agree to **GNU GPL 3** license it.
