const gulp = require("gulp");
const dartSass = require("sass");
const gulpSass = require("gulp-sass")(dartSass);

const postcss = require("gulp-postcss");
const autoprefixer = require("autoprefixer");

function styles() {
  const STYLE_SRC = [
    'scss/style.scss',
    'scss/paragraphs/**/*.scss',
    '!scss/**/_*.scss'
  ];

  return gulp
    .src(STYLE_SRC, { sourcemaps: true, base: 'scss' })
    .pipe(gulpSass({ outputStyle: "expanded" })
      .on("error", gulpSass.logError))
    .pipe(postcss([autoprefixer()]))
    .pipe(gulp.dest("css", { sourcemaps: "." }));
}

const watch = gulp.series(styles, function startWatch() {
  gulp.watch("scss/**/*.scss", styles);
});

exports.styles = styles;
exports.watch = watch;
exports.dev = watch;
