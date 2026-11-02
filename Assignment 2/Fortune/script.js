
/* ==========================================================================
   DAILY FORTUNE GENERATOR
   Complete JavaScript Code
   ========================================================================== */

// ==========================================================================
// 1. Fortune Messages Array
// ==========================================================================

const fortunes = [
    "আপনার জীবনে খুব শীঘ্রই নতুন কোনো সম্ভাবনার দরজা খুলতে যাচ্ছে।",
    "আজকের ছোট্ট একটি সিদ্ধান্ত আগামী দিনের বড় সাফল্যের কারণ হবে।",
    "ভাগ্য আপনার পক্ষেই আছে, শুধু সাহস করে এগিয়ে যান।",
    "অপ্রত্যাশিত কোনো মানুষের কাছ থেকে সুখবর পেতে পারেন।",
    "আপনার হাসিই কারও কঠিন দিনের সবচেয়ে সুন্দর উপহার।",
    "যে স্বপ্নটি অনেক দিন ধরে দেখছেন, তার পথে আপনি আগের চেয়েও এক ধাপ এগিয়ে।",
    "নতুন কোনো অভিজ্ঞতা আপনার জীবনের মোড় ঘুরিয়ে দিতে পারে।",
    "ধৈর্য ধরুন, আপনার অপেক্ষার ফল মিষ্টি হতে চলেছে।",
    "খুব শীঘ্রই এমন কিছু ঘটবে, যা আপনাকে আনন্দে অবাক করবে।",
    "আপনার জীবনে পুরোনো কোনো সম্পর্ক নতুন অর্থ নিয়ে ফিরে আসতে পারে।",
    "আজ কাউকে সাহায্য করুন, কাল ভাগ্য আপনাকেই সাহায্য করবে।",
    "আপনার প্রতিভা শিগগিরই সঠিক মানুষের নজরে আসবে।",
    "জীবনের সবচেয়ে সুন্দর অধ্যায়টি হয়তো এখনো লেখা বাকি।",
    "একটি নতুন পরিচয় আপনার জন্য নতুন সুযোগ নিয়ে আসবে।",
    "নিজের ওপর বিশ্বাস রাখুন, আপনি যতটা ভাবেন তার চেয়েও বেশি সক্ষম।",
    "আজকের কোনো অপ্রত্যাশিত ঘটনা ভবিষ্যতে সুন্দর স্মৃতি হয়ে থাকবে।",
    "অর্থ, সম্মান কিংবা ভালোবাসা—প্রত্যাশার চেয়ে সুন্দর কিছু অপেক্ষা করছে।",
    "ভুল পথে হাঁটার ভয় করবেন না, কখনো কখনো ভুল পথই সঠিক গন্তব্য দেখায়।",
    "আপনার জীবনে শিগগিরই এমন একটি দিন আসবে, যেদিন অপেক্ষার কারণ বুঝতে পারবেন।",
    "ভাগ্য আজ আপনাকে একটি ইঙ্গিত দিচ্ছে—সুযোগ এলে দুহাতে গ্রহণ করুন!"
];


// ==========================================================================
// 2. Color Palettes
// ==========================================================================

// Font Colors
const fontColors = [
    "#d12e1a",
    "#1e3a8a",
    "#166534",
    "#6b21a8",
    "#9a3412",
    "#0f766e",
    "#111827"
];

// Background Colors
const backgroundColors = [
    "#fced9b",
    "#dbeafe",
    "#dcfce7",
    "#fce7f3",
    "#ffedd5",
    "#f3e8ff",
    "#ffffff"
];

// Border Colors
const borderColors = [
    "#4f8b22",
    "#2563eb",
    "#dc2626",
    "#9333ea",
    "#ea580c",
    "#0d9488",
    "#374151"
];

// Font Styles
const fontStyles = [
    { family: "'Poppins', sans-serif", size: "1.15rem" },
    { family: "'Playfair Display', Georgia, serif", size: "1.25rem" },
    { family: "Georgia, serif", size: "1.2rem" },
    { family: "'Space Mono', monospace", size: "1.05rem" },
    { family: "'Trebuchet MS', sans-serif", size: "1.18rem" },
    { family: "Verdana, Geneva, sans-serif", size: "1.1rem" }
];


// ==========================================================================
// 3. Index Trackers
// ==========================================================================

let currentFontColorIndex = 0;
let currentBgColorIndex = 0;
let currentBorderColorIndex = 0;
let currentFontStyleIndex = 0;


// ==========================================================================
// 4. DOM Elements Selection
// ==========================================================================

const fortuneBox = document.getElementById("fortune-box");
const fortuneText = document.getElementById("fortune-text");

const btnFontColor = document.getElementById("btn-font-color");
const btnBgColor = document.getElementById("btn-bg-color");
const btnBorderColor = document.getElementById("btn-border-color");
const btnFontStyle = document.getElementById("btn-font-style");
const btnNewFortune = document.getElementById("btn-new-fortune");


// ==========================================================================
// 5. Check Whether All HTML Elements Exist
// ==========================================================================

if (
    !fortuneBox ||
    !fortuneText ||
    !btnFontColor ||
    !btnBgColor ||
    !btnBorderColor ||
    !btnFontStyle ||
    !btnNewFortune
) {
    console.error("Error: One or more HTML elements could not be found.");
}


// ==========================================================================
// 6. Random Fortune Selection
// ==========================================================================

function getRandomFortune() {
    const randomIndex = Math.floor(Math.random() * fortunes.length);

    return fortunes[randomIndex];
}

function displayRandomFortune() {
    const selectedFortune = getRandomFortune();

    fortuneText.textContent = selectedFortune;
}


// ==========================================================================
// 7. Button 1: Change Font Color
// ==========================================================================

btnFontColor.addEventListener("click", function () {

    currentFontColorIndex =
        (currentFontColorIndex + 1) % fontColors.length;

    fortuneText.style.color =
        fontColors[currentFontColorIndex];

    console.log("Font color changed!");
});


// ==========================================================================
// 8. Button 2: Change Background Color
// ==========================================================================

btnBgColor.addEventListener("click", function () {

    currentBgColorIndex =
        (currentBgColorIndex + 1) % backgroundColors.length;

    fortuneBox.style.backgroundColor =
        backgroundColors[currentBgColorIndex];

    console.log("Background color changed!");
});


// ==========================================================================
// 9. Button 3: Change Border Color
// ==========================================================================

btnBorderColor.addEventListener("click", function () {

    currentBorderColorIndex =
        (currentBorderColorIndex + 1) % borderColors.length;

    fortuneBox.style.borderColor =
        borderColors[currentBorderColorIndex];

    console.log("Border color changed!");
});


// ==========================================================================
// 10. Button 4: Change Font Style and Size
// ==========================================================================

btnFontStyle.addEventListener("click", function () {

    currentFontStyleIndex =
        (currentFontStyleIndex + 1) % fontStyles.length;

    const nextStyle = fontStyles[currentFontStyleIndex];

    fortuneText.style.fontFamily = nextStyle.family;

    fortuneText.style.fontSize = nextStyle.size;

    console.log("Font style changed!");
});


// ==========================================================================
// 11. Button 5: Generate Another Fortune
// ==========================================================================

btnNewFortune.addEventListener("click", function () {

    displayRandomFortune();

    console.log("New fortune generated!");
});


// ==========================================================================
// 12. Initialize the Website
// ==========================================================================

window.addEventListener("DOMContentLoaded", function () {

    displayRandomFortune();

    console.log("Daily Fortune Generator is ready!");

});
