# E2E testing

## Running the tests

Install dependencies first `yarn install --immutable`. This is done with your local node/yarm. This project runs on your local machine, not in a container.
To run the tests use `yarn run test` for visual or `yarn run test:hl` for headless.


When running them visually on Windows (wsl):

- Run cypress from a different checkout on your windows host system; this uses a browser on your host system as well.
- Use Windows 11 (or [use an X-server](https://nickymeuleman.netlify.app/blog/gui-on-wsl2-cypress)) and run cypress in your wsl distro.

### Regression sub-suite

Not every test can be run on a 'real' cluster, but a subset of tests can. The tests that can't are tagged (in the `it`) with the tag `@skip-regression`.
It is a good practice to run as much tests on the cluster as possible; but there are some reasons why you would omit one:
- It uses the underlying `docker-compose` setup, for setting environment variables.
- It is not yet able to handle a lot of data. On ci, the run starts fresh, but on qa there already is data. The test needs to be able to handle this.
- It is very flaky on qa environments and gives a lot of false positives.
- It adds no value to the regression test suite, or is too slow to justify the time it takes.

These tests can be run using `yarn run qa` and `yarn run qa:hl` to run against `www-dev` or with an extra flag `-c baseUrl=<url>` (like `yarn run qa:hl -c baseUrl=https://www-qa1.bco-portaal.nl/`) to run against any qa or acceptance environment.

To run the tests that are not included, for example to check if you can expand the subset, use the `yarn run noqa` en `yarn run noqa:hl` commands

There are no options to tag tests to run only against a cluster and not on ci. Creating this separate test suite would result in slower feedback on the functionality of these tests; creating even more flakyness in the proces. It is important to make sure that this regression suite is not full of false positives and flaky tests, as this would defeat the purpose of this suite to begin with: quick feedback on the deployment.

The flow that is encouraged:
- Deploy the branch you want to do regression testing on
- Locally checkout the same branch, so you have the matching tests
- Run the headless version
- Rerun the failing tests in the ui to understand what is happening. You have all your browser tools available to see what is going on.

**Next steps**

It would be great if this suite would run automatically on every deploy.

## Testing Strategy

To keep the e2e tests as fast and maintainable as possible, we opted for smaller and more focused tests, instead of long user flows. This goal also influences some choices in tooling and how we interact with the DOM.

**Test only what you want to prove**
 
The problem with flows, is that they have a large amount of overlap between each other. This will mean that within one suite, the same button will be clicked many times, with the same expected results (think about the login button for example).

What we do instead is setup our tests using the api helpers (located in `./support`) that are available on the `cy` object as commands. With these commands you can log in, create a case, create a task, etc. You can also avoid duplication user using `cy.visit()` instead of navigating pages with a button.

If you are missing a certain api endpoint to setup your test, you can add them as command and use an existing one as example. If you see that you are duplicating a certain clickpath, that is a good sign that you could add a command. Here, we want to stimulate code reuse because the api's are fairly stable. And if an api does change, there is only one place where we need to change tests.

**Test the integration of portal BE and FE**

Because E2E tests are relatively slow and expensive to maintain; we are not trying to build the largest suite, but the most effective one.

Before you write an E2E test, ask yourself the question 'what am I trying to prove here'? If you are solely trying to test interaction with the user, or the workings of the backend, you might better write a Vitest test or Laravel Feature test. When you want to prove the integration between BE and FE work correctly (and that is has the desired effect), adding a test here is the right choice.

Some interaction with the user needs the browser to be tested correctly (scrolling for example, is not testable in a virtual dom) you can add such test here as well. (There is not yet a better place for these tests) You can mock the backend if that is desirable. Avoid mixing real backend and mocks too much.

**Interact with the App like a user would**

With E2E tests we are proving that our system has certain capabilities that our users can utilize. Things that people care about is stuff like:
- Clicking on a button
- Reading a heading
- Seeing an error message
- Going to a new page

To query these things, we can use the accessibility of our app. Screenreaders rely on information in the markup such as Roles. When the app is usable by a screenreader, it is also easy to query in testcases.

The library that helps you write these user-centric queries is `Testing Library`.
- To learn more about [Testing Library queries](https://testing-library.com/docs/queries/about/)
- Use [this chrome extension](https://chrome.google.com/webstore/detail/testing-playground/hejbmebodbijjdhflfknehhcgaklhano?hl=en) to easily find testing library queries

**Focus your query on a specific context**

You don't have to query the whole page. Having too wide a search area might return multiple elements that are hard to distinguish, or somebody else might add an element at the other side of the page and suddenly have to deal with a failing test far away from their change.

If your tests focus on a particular component, you can add context with a test-id that you can use throughout your test.

**Query on keywords**

Querying on roles and text means that you have to be careful to not make overly specific queries. If certain text is prone to change (long sentences often are, or translations strings) it does not make a good candidate for a query. Instead you can use certain keywords that are contained in the text using a regex. If that too is hard, or likely to change, prefer a test-id above a long string.

**Don't hide away DOM queries**

The queries are the most volatile part of the suite. They are also the parts that are the hardest to isolate from other changes. Because we are avoiding long flows and duplication in user interactions, we are bringing these queries to the surface and are not abstracting them. The small cost in readability is easily recouped by the clarity of how the test is executed. 

## Tips and tricks

- Cypress runs in the browser; you have all the Developer Tools you normally use available
- The [Cypress docs](https://docs.cypress.io/guides/core-concepts/introduction-to-cypress) are very well maintained

## Problems on local runs

An issue with running the tests on your local dev env, is that they are not always identical to other dev env's or the CI runner. Keep in mind:
- Your environment variables
- Your local DB (`reset-db` can help here)

## Problems on CI runs

The CI runs do have consistent data en environment, but are somewhat low on resources. This might result in some flakyness. We try to minimise this by retrying a failed test once.

When a run fails there is some information you can use for tracking down issues:
- The console output contains a lot of information, such as the current dom-structure and alternative elements (with specific roles)
- In the artifacts, you can find a screenshot of the moment the test failed. If you see a spinner here, the runner might have been low on resources
- In the artifacts, you can find a dump of all the docker logs

### Reproducing on CI on your local machine

If you don't get sufficient info with the above methods, you can run the same docker setup on your machine.
To do this:
- Run `yarn run setup` in this workspace
- Run `yarn run ci:hl` for a full run and `yarn run ci` for the ui
- Afterwards clean up with `yarn run remove`

## Testing-library

We want to write maintainable tests that give us high confidence that your components are working for our users. As a part of this goal, we want your tests to avoid including implementation details so refactors of your components (changes to implementation but not functionality) don't break your tests and slow you and your team down. To achieve this we use some light-weight util functions from [Testing-library](https://testing-library.com/) to interact and query the DOM (in the browser).
