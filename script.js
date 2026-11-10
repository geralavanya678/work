

// Calculator display
const display = document.getElementById("theDisplay");
const history = document.getElementById("history");
const buttonContainer = document.getElementById("buttons");


// Calculator state
let currentInput = "";
let storedValue = null;
let pendingOperator = null;
let expression = "";
let justCalculated = false;
let error = false;


// Format numbers for display
function formatNumber(number) {

    if (!Number.isFinite(number)) {
        return "Error";
    }

    return Number.parseFloat(number.toPrecision(12)).toString();

}


// Update the display
function updateDisplay() {

    if (error) {
        display.value = "Error";
        return;
    }

    if (expression === "" && currentInput === "") {
        display.value = "0";
    } else {
        display.value = expression + currentInput;
    }

}


// Show an error
function showError() {

    display.value = "Error";
    history.textContent = "";
    error = true;

}


// Clear everything
function clearAll() {

    currentInput = "";
    storedValue = null;
    pendingOperator = null;
    expression = "";
    justCalculated = false;
    error = false;

    history.textContent = "";
    updateDisplay();

}


// Clear current number
function clearEntry() {

    if (error) {
        clearAll();
        return;
    }

    currentInput = "";
    updateDisplay();

}


// Add a number or decimal point
function updateNumber(value) {

    if (error) {
        clearAll();
    }

    if (justCalculated) {
        currentInput = "";
        expression = "";
        storedValue = null;
        pendingOperator = null;
        justCalculated = false;
        history.textContent = "";
    }

    if (value === "." && currentInput.includes(".")) {
        return;
    }

    if (value === "." && currentInput === "") {
        currentInput = "0";
    }

    if (currentInput.replace(".", "").length >= 12) {
        return;
    }

    currentInput += value;

    updateDisplay();

}


// Perform basic arithmetic
function calculate(a, b, operator) {

    switch (operator) {

        case "+":
            return a + b;

        case "-":
            return a - b;

        case "*":
            return a * b;

        case "/":
            if (b === 0) {
                return null;
            }
            return a / b;

        default:
            return b;

    }

}


// Press an operator
function handleOperator(operator) {

    if (error) {
        clearAll();
    }

    if (justCalculated) {
        storedValue = parseFloat(currentInput);
        expression = formatNumber(storedValue) + " ";
        currentInput = "";
        pendingOperator = operator;
        justCalculated = false;
        updateDisplay();
        return;
    }

    if (currentInput === "") {

        // Change the existing operator
        if (pendingOperator !== null) {
            pendingOperator = operator;
            expression =
                formatNumber(storedValue) + " " + operator + " ";
            updateDisplay();
        }

        return;
    }

    const number = parseFloat(currentInput);

    if (storedValue === null) {

        storedValue = number;

    } else if (pendingOperator !== null) {

        const result = calculate(
            storedValue,
            number,
            pendingOperator
        );

        if (result === null || !Number.isFinite(result)) {
            showError();
            return;
        }

        storedValue = result;

    }

    pendingOperator = operator;

    expression =
        formatNumber(storedValue) + " " + operator + " ";

    currentInput = "";

    updateDisplay();

}


// Calculate final result
function calculateResult() {

    if (error || justCalculated) {
        return;
    }

    if (
        storedValue === null ||
        pendingOperator === null ||
        currentInput === ""
    ) {
        return;
    }

    const secondValue = parseFloat(currentInput);

    const firstExpression =
        expression + currentInput;

    const result = calculate(
        storedValue,
        secondValue,
        pendingOperator
    );

    if (result === null || !Number.isFinite(result)) {
        showError();
        return;
    }

    history.textContent = firstExpression + " =";

    currentInput = formatNumber(result);
    expression = "";
    storedValue = null;
    pendingOperator = null;
    justCalculated = true;

    updateDisplay();

}


// Square root
function squareRoot() {

    if (error) {
        clearAll();
    }

    const number = currentInput === ""
        ? storedValue
        : parseFloat(currentInput);

    if (number === null || number === undefined) {
        return;
    }

    if (number < 0) {
        showError();
        return;
    }

    const result = Math.sqrt(number);

    currentInput = formatNumber(result);

    if (storedValue !== null && pendingOperator !== null) {
        expression =
            formatNumber(storedValue) + " " +
            pendingOperator + " ";
    } else {
        expression = "";
    }

    justCalculated = false;

    updateDisplay();

}


// Percentage
function percentage() {

    if (error) {
        clearAll();
    }

    if (currentInput === "") {
        return;
    }

    currentInput = formatNumber(
        parseFloat(currentInput) / 100
    );

    updateDisplay();

}


// Change positive/negative sign
function toggleSign() {

    if (error) {
        clearAll();
    }

    if (currentInput === "" && storedValue !== null) {
        currentInput = formatNumber(storedValue);
        storedValue = null;
        pendingOperator = null;
        expression = "";
    }

    if (currentInput === "" || parseFloat(currentInput) === 0) {
        return;
    }

    if (currentInput.startsWith("-")) {
        currentInput = currentInput.substring(1);
    } else {
        currentInput = "-" + currentInput;
    }

    updateDisplay();

}


// Delete last digit
function backspace() {

    if (error || justCalculated) {
        clearAll();
        return;
    }

    if (currentInput !== "") {
        currentInput = currentInput.slice(0, -1);
    }

    updateDisplay();

}


// Create a button
function createButton(value, className, action) {

    const button = document.createElement("button");

    button.textContent = value;
    button.className = className;

    button.addEventListener("click", action);

    buttonContainer.appendChild(button);

}


// Number and operator layout
const layout = [

    ["7", "number"],
    ["8", "number"],
    ["9", "number"],
    ["AC", "clear"],

    ["4", "number"],
    ["5", "number"],
    ["6", "number"],
    ["÷", "operator"],

    ["1", "number"],
    ["2", "number"],
    ["3", "number"],
    ["×", "operator"],

    ["+/-", "function"],
    ["0", "number"],
    [".", "number"],
    ["-", "operator"],

    ["√", "function"],
    ["%", "function"],
    ["CE", "function"],
    ["+", "operator"]

];


// Create all buttons
layout.forEach(function(item) {

    const value = item[0];
    const type = item[1];

    createButton(value, type, function() {

        if (type === "number") {
            updateNumber(value);
        }

        else if (value === "AC") {
            clearAll();
        }

        else if (value === "CE") {
            clearEntry();
        }

        else if (value === "√") {
            squareRoot();
        }

        else if (value === "%") {
            percentage();
        }

        else if (value === "+/-") {
            toggleSign();
        }

        else if (value === "÷") {
            handleOperator("/");
        }

        else if (value === "×") {
            handleOperator("*");
        }

        else {
            handleOperator(value);
        }

    });

});


// Backspace button
createButton("⌫", "function", backspace);


// Equals button
createButton("=", "equals", calculateResult);


// Keyboard support
document.addEventListener("keydown", function(event) {

    const key = event.key;

    if (key >= "0" && key <= "9") {
        updateNumber(key);
    }

    else if (key === ".") {
        updateNumber(".");
    }

    else if (["+", "-", "*", "/"].includes(key)) {
        handleOperator(key);
    }

    else if (key === "Enter" || key === "=") {
        calculateResult();
    }

    else if (key === "Backspace") {
        backspace();
    }

    else if (key === "Escape") {
        clearAll();
    }

    else if (key === "%") {
        percentage();
    }

});
