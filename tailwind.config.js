import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                "blanket-blue": {
                    // Azul oscuro del logo
                    DEFAULT: "#1D253A",
                    light: "#2F3C5A",
                },
                "blanket-yellow": {
                    // Amarillo/Lima del logo
                    DEFAULT: "#D4E03B",
                },
            },
        },
    },

    plugins: [forms],
};
