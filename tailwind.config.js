export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
  ],
  theme: {
    extend: {
      colors: {
        primary: "#4F6EF7",
        primaryHover: "#3F5FE0",
        secondary: "#6C8BFF",
        accent: "#22C1C3",
        success: "#22C55E",
        warning: "#F59E0B",
        danger: "#EF4444",
        background: "#F7F9FC",
        card: "#FFFFFF",
        border: "#E6EAF2",
        textPrimary: "#1F2937",
        textSecondary: "#6B7280",
        muted: "#9CA3AF",
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
        heading: ['Poppins', 'sans-serif'],
      },
      boxShadow: {
        'card': '0 10px 30px rgba(0,0,0,0.05)',
        'hover': '0 15px 40px rgba(0,0,0,0.08)',
        'modal': '0 25px 60px rgba(0,0,0,0.15)',
      },
      borderRadius: {
        'card': '16px',
        'btn': '10px',
        'input': '10px',
        'badge': '8px',
      }
    },
  },
  plugins: [
    require('flowbite/plugin')
  ],
}
