/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./templates/**/*.twig",
        "./**/*.theme",
        "./js/**/*.js",
        "./src/**/*.css"
    ],
    theme: {
        // автоматично задаємо параметри класу контейнер
        container: {
            center: true,
            padding: '1.5rem',
        },
        extend: {
            translate: {
                '120': '120%',
                '0':'0%'
            },
            // Перевизначення розмірної сітки
            screens: {
                '2xl': '1440px',
            },
            fontSize: {
                smallLogo: ['13px', {
                    lineHeight: '0.5',
                    letterSpacing: '0.8px',
                }],
                siteName: ['22px', {
                    lineHeight: '1',
                    letterSpacing: '0',
                }],
                slogan: ['15px', {
                    lineHeight: '1.2',
                    letterSpacing: '1.2px',
                }],
                mainMenu: ['17px', {
                    lineHeight: '100px',
                    letterSpacing: '0.5px',
                }],
                btn: ['13px', {
                    lineHeight: '1.2',
                    letterSpacing: '0.5px',
                }]
            },
            colors: {
                // палітра
                'theme-green': "#053426",
                'theme-bg-green': "rgba(0,35,26,0.85)",
                'theme-gold': "#B79259",
                'theme-gray': "#79847E",
                'theme-gold1': "#c2a25d",
                'theme-white': "#f9f9f9",
                'theme-white2': "#f7f9f9",
                'theme-gold2': "#b79259",
                'black': "#111",
                'black2': "#222",
                'bg-black':"#191611"
            },
            fontFamily: {
                // Основні шрифти
                "family-GD": ["Gilda Display"],
                "family-RH": ["Red Hat Display"],
                "family-PS": ["Pinyon Script", "cursive"],
            }
        },
    },
    plugins: [],
}

