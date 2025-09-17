const gulp = require("gulp");
const dartSass = require("sass");
const gulpSass = require("gulp-sass")(dartSass);
const tailwindcss = require("tailwindcss");

const postcss = require("gulp-postcss");
const autoprefixer = require("autoprefixer");

const STYLE_SRC = [
  "scss/style.scss",
  "scss/paragraphs/**/*.scss",
  "!scss/**/_*.scss"
];

function styles() {
  return gulp
    .src(STYLE_SRC, { sourcemaps: true, base: "scss" })
    .pipe(gulpSass({ outputStyle: "expanded" }).on("error", gulpSass.logError))
    .pipe(postcss([autoprefixer()]))
    .pipe(gulp.dest("css", { sourcemaps: "." }));
}

function tailwind() {
  return gulp
    .src("scss/tw.scss", { sourcemaps: true })
    .pipe(gulpSass({ outputStyle: "expanded" }).on("error", gulpSass.logError))
    .pipe(postcss([tailwindcss(), autoprefixer()]))
    .pipe(gulp.dest("css", { sourcemaps: "." }));
}

const watch = gulp.series(styles, tailwind, function startWatch() {
  gulp.watch("scss/**/*.scss", gulp.series(styles, tailwind));
  gulp.watch("tailwind.config.js", tailwind);
});

exports.tailwind = tailwind;
exports.styles = styles;
exports.watch = watch;
exports.dev = watch;
