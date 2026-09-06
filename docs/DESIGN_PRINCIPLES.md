# Design Principles

## 1. User-Centered Design

The interface should prioritize the user's task.

For example, Faculty should immediately understand:

```text
Subject
 ↓
Students
 ↓
Grades
 ↓
Review
 ↓
Submit
```

---

## 2. Role-Oriented Interfaces

Different roles should see relevant information.

### Admin

```text
Users
System Settings
Reports
Access Control
```

### Dean

```text
Faculty Assignments
Subjects
Grade Review
Approval
Reports
```

### Faculty

```text
Assigned Subjects
Students
Grading
Submission
```

### Student

```text
Grades
Whole Evaluation
Notifications
```

---

## 3. Consistency

Use consistent:

- Buttons
- Colors
- Typography
- Spacing
- Forms
- Tables
- Modals
- Toasts
- Icons
- Navigation

---

## 4. Clear Hierarchy

Important information should be visually prominent.

Example:

```text
Page Title
    ↓
Description
    ↓
Filters
    ↓
Primary Action
    ↓
Data
```

---

## 5. One Primary Action

Each page should have a clear primary action.

Example:

```text
Grade Encoding

[Save Draft]    [Submit Grading Sheet]
```

The submit action should be visually distinct because it has greater consequence.

---

## 6. Confirmation for Destructive Actions

Require confirmation for:

- Delete
- Reject
- Finalize
- Reset
- Remove assignment

Do not require confirmation for simple navigation.

---

## 7. Feedback

Every important action should provide feedback.

Examples:

```text
Saved successfully
Grade submitted
Student added
Changes updated
```

Use appropriate feedback:

- Toast for lightweight success
- Modal for important confirmation
- Inline validation for form errors
- Full error page for system-level errors

---

## 8. Prevent User Mistakes

Use:

- Disabled buttons while submitting
- Validation
- Confirmation dialogs
- Clear labels
- Status indicators
- Unsaved-change warnings when necessary

---

## 9. Mobile Friendly

The application should work on:

- Desktop
- Tablet
- Mobile

Tables should become horizontally scrollable or use responsive card/list layouts when appropriate.

---

## 10. Accessibility

Use semantic HTML.

Prefer:

```html
<button>
```

instead of:

```html
<div onclick="">
```

Forms should have labels.

Interactive controls must be keyboard accessible.

---

## 11. Status Must Be Obvious

Use clear status labels:

```text
Draft
Submitted
Under Review
Returned
Approved
Finalized
```

Avoid relying only on color.

---

## 12. Forms

Forms should:

- Have clear labels
- Show validation errors near fields
- Preserve valid entered data
- Indicate required fields
- Prevent accidental duplicate submission

---

## 13. Tables

Tables should prioritize:

1. Identification
2. Important status
3. Primary data
4. Actions

Avoid unnecessary columns.

---

## 14. Confirmation Language

Use action-specific messages.

Bad:

```text
Are you sure?
```

Good:

```text
Submit Grading Sheet?

Once submitted, the grading sheet will be sent to the Dean/Admin for review.
```

---

## 15. Error Design

Errors should explain:

```text
What happened
Why it happened
What the user can do
```

Example:

```text
Unable to submit grading sheet.

Some students do not have complete grades.

Please complete all required grades before submitting.
```
