/* Student Technology and Innovation Conference - registration logic.
   Keep these prices in step with the constants at the top of process.php. */

const REGISTRATION_FEE = 1000;
const DAILY_FEE = 500;
const WORKSHOP_FEE = 750;
const LUNCH_PER_DAY = 300;
const FINAL_YEAR = 4;
const FINAL_YEAR_DISCOUNT = 0.05;   // individual requirement: 5% for final-year students
const MIN_DAYS = 1;
const MAX_DAYS = 3;

function formatKsh(amount) {
    const hasCents = Math.round(amount * 100) % 100 !== 0;
    return "KSh " + amount.toLocaleString("en-KE", {
        minimumFractionDigits: hasCents ? 2 : 0,
        maximumFractionDigits: 2
    });
}

/* Show the workshop list only for Workshop Participants. */
function toggleWorkshop() {
    const category = document.getElementById("category").value;
    const box = document.getElementById("workshopBox");
    const select = document.getElementById("workshop");
    const isWorkshop = category === "workshop";

    box.hidden = !isWorkshop;
    select.disabled = !isWorkshop;   // disabled fields are not posted
    calculateFee();
}

/* Show meal options only when catering is ticked. */
function toggleMeals() {
    const wantsCatering = document.getElementById("catering").checked;
    document.getElementById("mealBox").hidden = !wantsCatering;
    document.getElementById("meal").disabled = !wantsCatering;
    calculateFee();
}

/* Describe the selected package in plain words. */
function showPackageMessage() {
    const category = document.getElementById("category").value;
    const days = parseInt(document.getElementById("days").value, 10) || 0;
    const catering = document.getElementById("catering").checked;
    const message = document.getElementById("packageMessage");

    if (category === "") {
        message.textContent = "Pick a category to see your package.";
        return;
    }
    const name = category === "workshop" ? "Workshop Package" : "Conference Only Package";
    message.textContent = name + ", " + days + (days === 1 ? " day" : " days") + (catering ? ", with catering" : "");
}

/* Work out the fee and update the summary panel. */
function calculateFee() {
    const days = parseInt(document.getElementById("days").value, 10);
    const validDays = days >= MIN_DAYS && days <= MAX_DAYS ? days : 0;
    const hasWorkshop = document.getElementById("category").value === "workshop";
    const hasLunch = document.getElementById("catering").checked && document.getElementById("meal").value === "lunch";
    const isFinalYear = parseInt(document.getElementById("year").value, 10) === FINAL_YEAR;

    const attendance = validDays * DAILY_FEE;
    const workshop = hasWorkshop ? WORKSHOP_FEE : 0;
    const meals = hasLunch ? validDays * LUNCH_PER_DAY : 0;
    const subtotal = REGISTRATION_FEE + attendance + workshop + meals;
    const total = isFinalYear ? subtotal * (1 - FINAL_YEAR_DISCOUNT) : subtotal;

    const lines = [
        ["Registration", REGISTRATION_FEE],
        ["Attendance (" + validDays + " x " + DAILY_FEE + ")", attendance],
        ["Workshop", workshop],
        ["Meals (" + (hasLunch ? validDays : 0) + " x " + LUNCH_PER_DAY + ")", meals]
    ];
    document.getElementById("feeBreakdown").innerHTML = lines
        .map(function (line) { return "<li><span>" + line[0] + "</span><strong>" + formatKsh(line[1]) + "</strong></li>"; })
        .join("");

    document.getElementById("discountNote").hidden = !isFinalYear;
    document.getElementById("totalFee").textContent = formatKsh(total);
    showPackageMessage();
}

function setError(fieldId, message) {
    document.getElementById(fieldId + "Error").textContent = message;
    const field = document.getElementById(fieldId);
    if (field) { field.classList.toggle("invalid", message !== ""); }
    return message === "";
}

/* Check every rule; block submission if any fail. */
function validateForm() {
    const name = document.getElementById("fullName").value.trim();
    const admission = document.getElementById("admissionNo").value.trim();
    const email = document.getElementById("email").value.trim();
    const phone = document.getElementById("phone").value.trim();
    const days = Number(document.getElementById("days").value);
    const category = document.getElementById("category").value;

    const results = [
        setError("fullName", name === "" ? "Enter your full name." : ""),
        setError("admissionNo", admission === "" ? "Enter your admission number." : ""),
        setError("email", /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email) ? "" : "Enter a valid email, like mary@example.com."),
        setError("phone", /^\d{10}$/.test(phone) ? "" : "Phone number must have exactly 10 digits."),
        setError("days", Number.isInteger(days) && days >= MIN_DAYS && days <= MAX_DAYS ? "" : "Choose between 1 and 3 days."),
        setError("category", category === "" ? "Select a participation category." : "")
    ];

    const allValid = results.every(Boolean);
    if (!allValid) {
        const firstBad = document.querySelector(".invalid");
        if (firstBad) { firstBad.focus(); }
    }
    return allValid;
}

/* Clear a field's error as soon as the user edits it. */
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll("#registrationForm input, #registrationForm select").forEach(function (el) {
        el.addEventListener("input", function () {
            const err = document.getElementById(el.id + "Error");
            if (err) { err.textContent = ""; el.classList.remove("invalid"); }
        });
    });
    calculateFee();
});
