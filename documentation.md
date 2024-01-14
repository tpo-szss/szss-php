# calc.js

## Overview

`calc.js` is a calculator script with functions for input manipulation, calculation, history tracking, and AJAX request handling.

## Variables

- `currentInputCalc`: Current calculator input.
- `lastResult`: Last calculated result.
- `calcHistory`: Calculation history from local storage or empty array.
- `calculated`: Indicates if a calculation has been made.

## Functions

### `appendNumber(number)`

Appends a number to the current input and updates the result.

### `appendOperator(operator)`

Appends an operator to the current input and updates the result.

### `appendDecimal()`

Appends a decimal point to the current input if not present and updates the result.

### `clearInput()`

Clears the current input and updates the result.

### `deleteDigit()`

Deletes the last digit from the current input and updates the result.

### `calculateResult()`

Calculates the result of the current input, updates history and result if successful, or sets input to "Napaka" if failed.

### `toggleSign()`

Toggles the sign of the current input and updates the result.

### `updateResult()`

Updates the result displayed on the page.

### `updateHistory()`

Updates the history displayed on the page.

### `saveHistory()`

Saves the history to local storage.

### `xhrCalcResult()`

Sends an AJAX request to add a transaction, shows notifications based on the request result.

## Event Listeners

- `window.onload`: Updates history on window load.
- `document.keydown`: Handles keydown events.

# calc-plus.js

## Overview

`calc-plus.js` is a calculator script with functions for input manipulation, calculation, history tracking, AJAX request handling, and draggable UI.

## Variables

- `currentInputCalc`: Current calculator input.
- `lastResult`: Last calculated result.
- `calcHistory`: Calculation history from local storage or empty array.
- `calculated`: Indicates if a calculation has been made.

## Functions

### `dragElement(elmnt)`

Makes a DOM element draggable.

### `makeVisible()`

Toggles the visibility of the element with id "mydiv".

### `appendNumberZ(number)`

Appends a number to the current input and updates the result.

### `appendOperatorZ(operator)`

Appends an operator to the current input and updates the result.

### `appendDecimalZ()`

Appends a decimal point to the current input if not present and updates the result.

### `clearInputZ()`

Clears the current input and updates the result.

### `deleteDigitZ()`

Deletes the last digit from the current input and updates the result.

### `calculateResultZ()`

Calculates the result of the current input, updates history and result if successful, or sets input to "Napaka" if failed.

### `toggleSignZ()`

Toggles the sign of the current input and updates the result.

### `updateResultZ()`

Updates the result displayed on the page.

### `updateHistoryZ()`

Updates the history displayed on the page.

### `saveHistory()`

Saves the history to local storage.

### `xhrCalcResultZ()`

Sends an AJAX request to add a transaction, shows notifications based on the request result.

## Event Listeners

- `window.onload`: Updates history on window load.
- `document.onmousedown`: Handles mousedown events for dragging.
- `document.onmouseup`: Handles mouseup events for dragging.
- `document.onmousemove`: Handles mousemove events for dragging.

# home.js

## Overview

`home.js` is a JavaScript file designed to manage financial transactions on a web application. It features functions for loading, viewing, adding, editing, and deleting transactions, managing sandboxes (virtual accounts), updating balances, handling user interactions, and generating graphical representations of transaction data.

## Variables

- `notyf`: An instance of Notyf for user notifications.
- `balance`: Stores the current account balance.
- `transactions`: An array to hold transaction details.
- `sandbox`: Array for managing multiple account simulations (sandboxes).
- `listType`: Variable to store the type of transaction list to display.
- `sandboxId`: Identifier for the current sandbox.
- `sandboxName`: Name of the current sandbox.
- `searchForm`: Reference to the search form element.
- `transactionsPerPage`: Number of transactions to display per page.

## Functions

### `loadTransactions(order, search)`

Loads transactions based on specified order and search criteria. Sends an AJAX request and updates the UI with the received data.

### `arraysAreEqual(arr1, arr2)`

Compares two arrays for equality.

### `drawGraph(animate)`

Generates and updates graphical representations (line and bar charts) of transactions.

### `loadSandbox()`

Loads sandbox data and updates the dropdown menu with sandbox options.

### `populateDropdownMenu()`

Populates the sandbox dropdown menu with available sandboxes.

### `mainSandbox()`

Switches to the main sandbox and reloads transactions.

### `deleteSandbox()`

Deletes the current sandbox after confirmation.

### `addTransaction()`

Adds a new transaction to the list and sends data to the server.

### `getCurrentPage()`

Returns the current page number for pagination.

### `setCurrentPage(page)`

Sets the current page number for pagination.

### `updateTransactionList(page, search)`

Updates the list of transactions displayed on the UI.

### `addPaginationControls(currentPage, totalPages)`

Adds pagination controls based on the current page and total pages.

### `deleteTransaction(x)`

Deletes a transaction after confirmation.

### `editTransaction(x)`

Edits an existing transaction and updates it on the server.

### `odstraniDatoteko()`

Removes a selected file (used in transaction editing).

### `addSandbox()`

Adds a new sandbox and loads its transactions.

### `viewTransaction(x)`

Redirects to view details of a specific transaction.

### `updateBalance()`

Updates the displayed account balance.

### `showTransactions(type)`

Filters and shows transactions based on the selected type (all, inflow, outflow).

### `addItem(list, inputField)`

Adds a new item to a specified list.

### `populateHitriVnosiMenu()`

Populates a menu with quick input options.

### `ponastaviPogled()`

Resets the transaction view to default settings.

### `sortABC()`, `najvisjaVrednost()`, `najnizjaVrednost()`, `najvisjiPriliv()`, `najnizjiPriliv()`, `najvisjiOdliv()`, `najnizjiOdliv()`

Various sorting functions to order transactions based on different criteria.

### `napolniFilter()`

Populates the filter dropdown with various filtering options.

## Event Listeners

- `searchForm.addEventListener`: Listens for input on the search form and reloads transactions based on the search term.
- Various button and link click events to trigger corresponding functions, such as adding, deleting, viewing, and editing transactions, managing sandboxes, and applying filters and sorts.

# other.js

## Overview

`other.js` is a JavaScript script focused on managing 'sandbox' accounts in a financial application. It includes functionalities for loading, adding, deleting, and switching between sandboxes, as well as interacting with the server via AJAX requests. The script also utilizes user notifications and confirmation dialogs for better user experience.

## Variables

- `notyf`: Instance of Notyf for displaying notifications.
- `sandbox`: Array to store sandbox account details.

## Functions

### `loadSandbox()`

Sends an AJAX POST request to load sandbox account details. Upon successful response, it populates the sandbox dropdown menu.

### `populateDropdownMenu()`

Dynamically creates and populates the dropdown menu with sandbox accounts. Includes functionality to switch to the main account, add new sandboxes, and delete the current sandbox.

### `mainSandbox()`

Switches to the main sandbox account. Sends an AJAX request and redirects to the home page upon success.

### `deleteSandbox()`

Opens a confirmation dialog using Swal. On confirmation, sends an AJAX request to delete the current sandbox account and switches to the main account upon success.

### `addSandbox()`

Opens a dialog for the user to input the name of a new sandbox. Sends an AJAX request to add the new sandbox and switches to it upon successful addition.

## User Interactions and Notifications

- Utilizes `Swal.fire()` for confirmation dialogs before deleting a sandbox.
- Uses `Notyf` for success and error notifications throughout the script.
- Redirects to different pages or reloads the current page upon successful operations like adding or deleting a sandbox.

## AJAX Request Handling

- All server interactions are handled using AJAX requests, specifically with `XMLHttpRequest`.
- Request URLs are constructed with appropriate parameters and sent to `action.php` with different actions (`peskovnik&izberi`, `peskovnik&izbrisi`, `peskovnik&dodaj`).

## Notes

- This script is designed to work as part of a larger web application, handling a specific feature related to financial sandbox accounts.
- It assumes the presence of other components and server-side scripts (`action.php`) for full functionality.

# pogled.js

## Overview

`pogled.js` is a JavaScript file designed to manage user preferences for view layout and theme in a web application. It provides functionality to save, set, and retrieve user-selected view and theme settings using local storage.

## Variables

- `izbranPogled`: Stores the user's selected view setting, retrieved from local storage or defaults to an empty array.
- `izbranaTema`: Stores the user's selected theme, retrieved from local storage or defaults to "navadno" (normal).

## Functions

### `shraniPogled()`

Saves the current view setting (`izbranPogled`) to local storage and reloads the page to apply the setting.

### `nastaviPogled()`

Sets the view based on the user's selection. It checks which view radio button is checked, updates `izbranPogled`, and calls `shraniPogled()` to save and apply

the change. 

### `shraniTemo()`

Saves the current theme setting (`izbranaTema`) to local storage, marks the selected theme as checked, and reloads the page to apply the new theme.

### `nastaviTemo()`

Determines the selected theme based on which theme radio button is checked. It updates `izbranaTema` with the selected theme and calls `shraniTemo()` to save and apply the change.

### `poisciChecked()`

Ensures that the currently selected theme and view settings are marked as checked when the page loads or when settings are changed. This function enhances user experience by reflecting the current settings visibly in the UI.

### `shraniSpremembe()`

Reloads the page. This function can be used to apply changes that require a page refresh to take effect.

## User Interactions and Data Storage

- Radio buttons or similar UI elements are expected for users to select their preferred view and theme settings.
- Utilizes `localStorage` for persisting user preferences across sessions, ensuring that their chosen settings are remembered and applied on subsequent visits to the application.

## Implementation Details

- The script assumes the presence of specific elements with IDs corresponding to different views and themes (e.g., `sirse`, `zgosceno`, `navadno`, `temno`, `modro`, `rumeno`).
- The use of `console.log()` helps in debugging and verifying the selected options.
- This script is part of a larger application and works in conjunction with other components and scripts for full functionality.

# trans.js

## Overview

`trans.js` is a JavaScript file designed to manage date selection and display based on the type of transaction recurrence (one-time, daily, weekly, monthly, yearly) in a web application. It includes functions for showing and hiding date pickers, determining active tabs for recurrence selection, and compiling final date choices.

## Functions

### `dodajKoncniDatum()`

Toggles the visibility of the daily end date calendar (`koledar`) based on the state of the daily checkbox (`flexCheckDefault`).

### `dodajKoncniDatumWeek()`

Toggles the visibility of the weekly end date calendar (`koledar`) based on the state of the weekly checkbox (`flexCheckDefaultWeek`).

### `dodajKoncniDatumMonth()`

Toggles the visibility of the monthly end date calendar (`koledar`) based on the state of the monthly checkbox (`flexCheckDefaultMonth`).

### `dodajKoncniDatumYear()`

Toggles the visibility of the yearly end date calendar (`koledar`) based on the state of the yearly checkbox (`flexCheckDefaultYear`).

### `naredi()`

Determines the type of recurrence (`kaj`) and start (`zacDat`) and end (`koncDatum`) dates based on the active tab in the UI. Defaults to January 1, 2024, if no start date is selected. Returns an array with these values.

### `narediVec()`

Invokes `naredi()` to obtain recurrence type and dates. Sets these values into corresponding input fields (`ponavljanjeID`, `startDatumID`, `endDatumID`) and displays a success notification.

## User Interaction and Dynamic Content

- The script listens for changes in checkboxes and tab selections to dynamically show or hide date pickers.
- It uses `console.log()` for debugging and verifying user choices.

## Notifications

- Utilizes `notyf` to display success notifications after saving the recurrence settings.

## Notes

- This script assumes the presence of specific HTML elements (checkboxes, input fields, and tabs) with predefined IDs.
- It is designed to work within a larger application, possibly a task or event scheduler, where users can set up recurring events.