/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "templates/**/*.html.twig",
        "/js/*.js",
    ],
    theme: {
        extend: {
            colors: {
                custom_color_4: "#c2a25d",
                custom_color_5: "#f7f9f9",
                custom_color_1: "#053426",
                custom_color_2: "#B79259",
                custom_color_3: "#79847E",
                theme_color: "#b79259",
                slider_text: "rgba(183,146,89,0.7)",
                black: "#222",
                text_color: "#111",
            },
            fontFamily: {
                def_font_1: "'Open Sans','Open Sans Regular',sans-serif",
                def_font_2: "'Roboto',sans-serif",
                font_global_1: "Gilda Display",
                font_global_2: "Red Hat Display",
                font_global_3: "Pinyon Script",
            }
        },
    },
    plugins: [],
}

