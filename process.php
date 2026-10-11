<?php
/* Server-side processing. The total is recalculated here; the browser's figure is never trusted.
   Keep these prices in step with js/script.js. */
const REGISTRATION_FEE = 1000;
const DAILY_FEE = 500;
const WORKSHOP_FEE = 750;
const LUNCH_PER_DAY = 300;
const FINAL_YEAR = 4;
const FINAL_YEAR_DISCOUNT = 0.05;
const MIN_DAYS = 1;
const MAX_DAYS = 3;
const WORKSHOPS = ['Web Development', 'Artificial Intelligence', 'Cyber Security'];

function clean($value) {
    return trim((string) $value);
}

function showPage($title, $body) {
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">'
       . '<meta name="viewport" content="width=device-width, initial-scale=1">'
       . '<title>' . htmlspecialchars($title) . '</title>'
       . '<link rel="stylesheet" href="css/style.css"></head><body>'
       . '<header class="site-header"><a class="brand" href="index.html">STIC 2026</a>'
       . '<nav><a href="index.html">Home</a><a href="register.html">Register</a></nav></header>'
       . '<main class="confirm-wrap">' . $body . '</main>'
       . '<footer class="site-footer">Student Technology and Innovation Conference 2026</footer></body></html>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    showPage('No data', '<div class="confirm-card"><h1>No registration received</h1><p>Please fill in the form first.</p><a class="btn btn-primary" href="register.html">Go to registration</a></div>');
}

// 1. Receive values (missing checkboxes and disabled fields simply default).
$fullName    = clean($_POST['fullName'] ?? '');
$admissionNo = clean($_POST['admissionNo'] ?? '');
$email       = clean($_POST['email'] ?? '');
$phone       = clean($_POST['phone'] ?? '');
$year        = (int) ($_POST['year'] ?? 0);
$category    = clean($_POST['category'] ?? '');
$days        = (int) ($_POST['days'] ?? 0);
$workshop    = clean($_POST['workshop'] ?? '');
$catering    = ($_POST['catering'] ?? '') === 'yes';
$meal        = clean($_POST['meal'] ?? 'none');

// 2 and 3. Essential values and server-side validation.
$errors = [];
if ($fullName === '')                                  { $errors[] = 'Full name is required.'; }
if ($admissionNo === '')                               { $errors[] = 'Admission number is required.'; }
if (!filter_var($email, FILTER_VALIDATE_EMAIL))        { $errors[] = 'Email address is not valid.'; }
if (!preg_match('/^\d{10}$/', $phone))                 { $errors[] = 'Phone number must have exactly 10 digits.'; }
if ($year < 1 || $year > 4)                            { $errors[] = 'Year of study is not valid.'; }
if ($days < MIN_DAYS || $days > MAX_DAYS)              { $errors[] = 'Conference days must be between 1 and 3.'; }
if (!in_array($category, ['conference', 'workshop'], true)) { $errors[] = 'Select a participation category.'; }
if ($category === 'workshop' && !in_array($workshop, WORKSHOPS, true)) { $errors[] = 'Select a valid workshop.'; }

if ($errors) {
    $list = '';
    foreach ($errors as $e) { $list .= '<li>' . htmlspecialchars($e) . '</li>'; }
    showPage('Registration problem', '<div class="confirm-card failed"><h1>REGISTRATION FAILED</h1><ul>' . $list . '</ul><a class="btn btn-primary" href="register.html">Go back and fix</a></div>');
}

// 4. Independent fee calculation.
$hasWorkshop = $category === 'workshop';
$hasMeals    = $catering && $meal === 'lunch';
$subtotal    = REGISTRATION_FEE + ($days * DAILY_FEE)
             + ($hasWorkshop ? WORKSHOP_FEE : 0)
             + ($hasMeals ? $days * LUNCH_PER_DAY : 0);
$discount    = ($year === FINAL_YEAR) ? $subtotal * FINAL_YEAR_DISCOUNT : 0;
$total       = $subtotal - $discount;

function ksh($n) {
    return 'KSh ' . (fmod($n, 1) == 0 ? number_format($n) : number_format($n, 2));
}
$h = 'htmlspecialchars';

// 5. Personalized confirmation.
$body  = '<div class="confirm-card"><h1>REGISTRATION SUCCESSFUL</h1><dl>';
$body .= '<dt>Participant</dt><dd>' . $h($fullName) . '</dd>';
$body .= '<dt>Admission Number</dt><dd>' . $h($admissionNo) . '</dd>';
$body .= '<dt>Email</dt><dd>' . $h($email) . '</dd>';
$body .= '<dt>Year of Study</dt><dd>Year ' . $year . '</dd>';
$body .= '<dt>Conference Days</dt><dd>' . $days . '</dd>';
$body .= '<dt>Workshop</dt><dd>' . ($hasWorkshop ? $h($workshop) : 'None') . '</dd>';
$body .= '<dt>Meals</dt><dd>' . ($hasMeals ? 'Yes' : 'No') . '</dd>';
if ($discount > 0) {
    $body .= '<dt>Final-year discount (5%)</dt><dd>- ' . ksh($discount) . '</dd>';
}
$body .= '</dl><p class="grand">TOTAL AMOUNT PAYABLE: <strong>' . ksh($total) . '</strong></p>';
$body .= '<p>Thank you for registering for the<br>Student Technology and Innovation Conference.</p>';
$body .= '<a class="btn btn-primary" href="index.html">Back to home</a></div>';
showPage('Registration confirmed', $body);
