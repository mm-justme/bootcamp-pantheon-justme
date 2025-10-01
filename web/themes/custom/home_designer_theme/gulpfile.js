const gulp = require("gulp");
const dartSass = require("sass");
const gulpSass = require("gulp-sass")(dartSass);
const tailwindcss = require("tailwindcss");

const postcss = require("gulp-postcss");
const autoprefixer = require("autoprefixer");

const browserSync = require("browser-sync").create();

const rename = require("gulp-rename");

const STYLE_SRC = [
  "scss/style.scss",
  "scss/paragraphs/**/*.scss",
  "!scss/**/_*.scss"
];
const LOCAL_URL = "https://bootcamp-pantheon-justme.ddev.site";

function styles() {
  return gulp
    .src(STYLE_SRC, { sourcemaps: true, base: "scss" })
    .pipe(
      gulpSass({ outputStyle: "expanded", quietDeps: true }).on(
        "error",
        gulpSass.logError
      )
    )
    .pipe(postcss([autoprefixer()]))
    .pipe(gulp.dest("css", { sourcemaps: "." }))
    .pipe(browserSync.stream({ match: "**/*.css" })); // injection for scss (helps avoid  full reload)
}

function tailwind() {
  return gulp
    .src("scss/tw.scss", { sourcemaps: true })
    .pipe(postcss([tailwindcss(), autoprefixer()]))
    .pipe(rename({ basename: "tw", extname: ".css" })) // deleted pipe for scss to css in tailwind function. We don't need it, we don't use scss in tailwind files. That's why we use rename to save file in css format.
    .pipe(gulp.dest("css", { sourcemaps: "." }))
    .pipe(browserSync.stream({ match: "**/*.css" })); // injection for tailwind (helps avoid  full reload)
}

function serve() {
  browserSync.init({
    proxy: LOCAL_URL,
    open: false,
    notify: false,
    ghostMode: false
  });

  // Separate watcher for scss and tailwind

  gulp.watch(["scss/**/*.scss", "!scss/tw.scss", "scss/components/ckeditor5.css"], styles);
  gulp.watch(
    [
      "scss/tw.scss",
      "tailwind.config.js",
      "templates/**/*.twig",
      "**/*.theme",
      "js/**/*.js"
    ],
    tailwind
  );

  gulp
    .watch(["templates/**/*.twig", "**/*.theme", "js/**/*.js"])
    .on("change", browserSync.reload);
}

exports.serve = serve;
exports.dev = gulp.series(styles, tailwind, serve);
