let gulp = require('gulp'),
    sass = require('gulp-sass')(require('sass')),
    sourcemaps = require('gulp-sourcemaps'),
    $ = require('gulp-load-plugins')(),
    cleanCss = require('gulp-clean-css'),
    rename = require('gulp-rename'),
    postcss = require('gulp-postcss'),
    autoprefixer = require('autoprefixer'),
    postcssInlineSvg = require('postcss-inline-svg'),
    browserSync = require('browser-sync').create(),
    pxtorem = require('postcss-pxtorem'),
    { exec } = require('child_process');        // ⬅️ додали
const tailwindcss = require('tailwindcss');

const postcssProcessors = [
    postcssInlineSvg({
        removeFill: true,
        paths: ['./node_modules/bootstrap-icons/icons'],
    }),
    pxtorem({
        propList: ['font','font-size','line-height','letter-spacing','*margin*','*padding*'],
        mediaQuery: true,
    }),
];

const paths = {
    scss: {
        src: './scss/style.scss',
        dest: './css',
        watch: './scss/**/*.scss',
        bootstrap: './node_modules/bootstrap/scss/bootstrap.scss',
    },
    js: {
        bootstrap: './node_modules/bootstrap/dist/js/bootstrap.min.js',
        popper: './node_modules/@popperjs/core/dist/umd/popper.min.js',
        base: '../../contrib/bootstrap/js/base.js',
        dest: './js',
    },
};

// ===== SASS =====
function styles() {
    return gulp
        .src([paths.scss.bootstrap, paths.scss.src])
        .pipe(sourcemaps.init())
        .pipe(
            sass({
                includePaths: ['./node_modules/bootstrap/scss', '../../contrib/bootstrap/scss'],
            }).on('error', sass.logError)
        )
        .pipe($.postcss(postcssProcessors))
        .pipe(postcss([
            autoprefixer({
                browsers: [
                    'Chrome >= 35','Firefox >= 38','Edge >= 12','Explorer >= 10',
                    'iOS >= 8','Safari >= 8','Android 2.3','Android >= 4','Opera >= 12',
                ],
            }),
        ]))
        .pipe(sourcemaps.write())
        .pipe(gulp.dest(paths.scss.dest))
        .pipe(cleanCss())
        .pipe(rename({ suffix: '.min' }))
        .pipe(gulp.dest(paths.scss.dest))
        .pipe(browserSync.stream());
}

// ===== JS copy =====
function js() {
    return gulp
        .src([paths.js.bootstrap, paths.js.popper, paths.js.base])
        .pipe(gulp.dest(paths.js.dest))
        .pipe(browserSync.stream());
}

// ===== Tailwind build =====
function tailwind() {
    return gulp.src('src/tw.css')
        .pipe(postcss([ tailwindcss(), autoprefixer(), ...postcssProcessors ])
            .on('error', function (err) { console.error(err.toString()); this.emit('end'); }))
        .pipe(gulp.dest('css'))
        .pipe(browserSync.stream({ match: '**/*.css' }));
}

// ===== Drush CR (послідовно після Tailwind) =====
function drushCr(done) {
    exec('ddev drush cr', (err, stdout, stderr) => {
        if (err) { console.error(stderr || err); return done(err); }
        console.log(stdout);
        done();
    });
}

// ===== Reload =====
function reload(done) { browserSync.reload(); done(); }

// ===== Serve + Watch (єДИНИЙ ВОТЧЕР) =====
const LOCAL_URL = 'https://bootcamp-pantheon-justme.ddev.site';

function serve() {
    browserSync.init({ proxy: LOCAL_URL, open: false });

    // SASS
    gulp.watch([paths.scss.watch, paths.scss.bootstrap], styles).on('change', browserSync.reload);

    // Tailwind + Twig/Theme/JS/Config → послідовно: tw → drush cr → reload
    gulp.watch(
        ['src/**/*.css','templates/**/*.twig','**/*.theme','js/**/*.js','tailwind.config.js'],
        { delay: 400 },
        gulp.series(tailwind, drushCr, reload)
    );
}

// ===== Entrypoints =====
const build = gulp.series(gulp.parallel(styles, tailwind), gulp.parallel(js, serve));
exports.styles = styles;
exports.js = js;
exports.tailwind = tailwind;
exports.serve = serve;
exports.default = build;
