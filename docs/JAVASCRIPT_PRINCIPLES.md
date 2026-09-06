# JavaScript Principles

## 1. Vanilla JavaScript

The frontend uses native JavaScript unless a package provides a significant benefit.

Prefer:

```js
document.querySelector()
fetch()
FormData
addEventListener()
classList
```

---

## 2. No Inline JavaScript

Avoid:

```html
<button onclick="deleteStudent(1)">
```

Use:

```js
button.addEventListener('click', deleteStudent);
```

---

## 3. Modules

JavaScript should be organized into modules.

```text
public/assets/js/
├── app.js
├── components/
│   ├── modal.js
│   ├── toast.js
│   └── dropdown.js
└── pages/
    ├── grading.js
    └── students.js
```

---

## 4. Page-Specific JavaScript

Do not load every JavaScript feature on every page.

Example:

```text
grading.php
    ↓
grading.js
```

Student management:

```text
students.php
    ↓
students.js
```

---

## 5. DOM Queries

Cache frequently used elements.

Good:

```js
const form = document.querySelector('#grading-form');
const submitButton = form.querySelector('[type="submit"]');
```

---

## 6. Event Delegation

For dynamic lists, prefer event delegation.

```js
container.addEventListener('click', (event) => {
    const button = event.target.closest('[data-action="delete"]');

    if (!button) return;

    deleteStudent(button.dataset.id);
});
```

---

## 7. Fetch

Use `fetch()` for asynchronous requests.

```js
const response = await fetch('/faculty/grading/save', {
    method: 'POST',
    body: formData,
});
```

Always handle errors.

---

## 8. Loading State

Prevent duplicate submissions.

```js
button.disabled = true;

try {
    await saveGrades();
} finally {
    button.disabled = false;
}
```

---

## 9. API Responses

Prefer consistent JSON responses.

Example:

```json
{
    "success": true,
    "message": "Grades saved successfully.",
    "data": {}
}
```

Error:

```json
{
    "success": false,
    "message": "Unable to save grades.",
    "errors": {}
}
```

---

## 10. XSS Prevention

Never inject untrusted values directly with:

```js
element.innerHTML = userInput;
```

Prefer:

```js
element.textContent = userInput;
```

When HTML is genuinely required, sanitize or construct trusted DOM elements.

---

## 11. UI State

JavaScript controls:

- Modal visibility
- Loading states
- Toasts
- Tabs
- Filters
- Dropdowns
- Dynamic forms

PHP remains responsible for authorization and business rules.

---

## 12. No Security Logic in JavaScript

Never rely on:

```js
if (user.role === 'ADMIN') {
    showDeleteButton();
}
```

as the actual security mechanism.

The server must independently enforce authorization.

---

## 13. Progressive Enhancement

Important functionality should still work when possible without JavaScript.

JavaScript should enhance the experience rather than become the only security boundary.

---

## 14. Naming

Use:

```text
camelCase
```

for JavaScript variables and functions.

Examples:

```js
const gradingSheetId = 10;

function submitGradingSheet() {}
function calculateFinalGrade() {}
```

Classes use:

```text
PascalCase
```

---

## 15. Avoid Global Variables

Prefer modules and local scope.

Avoid:

```js
window.currentStudent = ...
```

unless intentionally exposing a public API.
