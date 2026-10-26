# CSE 391 — Assignment 1: Personal Web Page

A personalized, responsive, multi-page personal website for Md. Ishtiaq Mozumder. It is built with HTML, an external CSS stylesheet, and vanilla JavaScript.

## Files

- `index.html` — homepage, personal information, favorite quote, categorized interests, anchor links, external links, and contact form.
- `about.html` — background, education, experience, and skills.
- `interests.html` — interests and a numbered learning list.
- `projects.html` — selected experiences and a responsive striped table with borders, multi-line cells, `rowspan`, and `colspan`.
- `css/style.css` — overall styling, responsive breakpoints, hover effects, animations, and dark-mode styles.
- `js/script.js` — dark/light toggle, contact-form email draft, current page URL, last-modified date, and copyright year.
- `images/profile-placeholder.svg` — local illustrated profile placeholder.
- `images/web-illustration.svg` — second locally stored image.

## Before submitting — personalize these items

1. **Use your own photograph.** The assignment explicitly asks for your own photo. The included SVG is only a placeholder because no personal photo was supplied. Add a photo such as `images/profile.jpg` and change the `src` of the profile `<img>` in `index.html` and `about.html` to `images/profile.jpg`. Keep a copy inside the submitted ZIP.
2. **Replace the contact email.** Search for `your.email@example.com` in `index.html` and `js/script.js` and replace it with your real email address. The form opens a draft in the visitor's default email app; it cannot send an email directly from a static website without a server or form service.
3. **Replace the CSE 391 Discord link.** The assignment brief did not include the official invite URL. The homepage currently labels a general Discord link as a placeholder; change its `href` in `index.html` to the actual course server/invite shared by your instructor before submission.
4. Review the biography and approximate community-project totals to make sure you want them public and that they are up to date.
5. Open every page and test the navigation, dark/light mode, links, and contact form on your browser.

## Run locally

Unzip the file and open `index.html` in a browser. No build tools or package installations are needed. The Google Fonts import requires an internet connection; fallback system fonts are defined if it is unavailable.

## Assignment requirement checklist

- [x] Personal info and categorized interest links
- [x] Prominent name and multiple clearly labeled sections
- [x] Same-page anchor navigation
- [x] Internal links to `about.html`, `interests.html`, and `projects.html`
- [x] External link to BRAC University and other reference websites
- [ ] Replace the profile placeholder with your own photograph
- [x] Favorite quote and lists
- [x] More than five hyperlinks; one is embedded in a sentence
- [ ] Replace the homepage Discord placeholder URL with the official CSE 391 invite
- [x] Two locally stored image files
- [x] Structured table with multi-line cells, merged rows/columns, borders, striped rows, hover styling, and responsive horizontal overflow
- [x] External CSS, one small internal style block, and one inline style
- [x] JavaScript page-location and last-modified display
- [x] DOCTYPE, HTML/head/body, meta tags, title, header, footer, and copyright
- [x] Bonus: contact form email draft, dark/light mode, and CSS animations

## Notes on validation

The pages use the XHTML 1.0 Strict doctype requested in the assignment. Check each page with the [W3C Markup Validation Service](https://validator.w3.org/), then address any warnings/errors from your course validator. CSS uses modern responsive layout and animation features supported by current browsers.
