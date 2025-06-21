document.addEventListener('DOMContentLoaded', () => {
    const scrollBtn = document.querySelector('.scroll-top-btn');

    // Botão de subir
    if (scrollBtn) {
        scrollBtn.style.display = 'none';

        window.addEventListener('scroll', () => {
            scrollBtn.style.display = window.scrollY > 200 ? 'block' : 'none';
        });

        scrollBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // Filtro de Skill Balance
    const filterBtn = document.getElementById('filter-skill');
    const resetBtn = document.getElementById('reset-filter');

    if (filterBtn && resetBtn) {
        filterBtn.addEventListener('click', () => {
            document.querySelectorAll('.patch-card').forEach(card => {
                const summary = card.querySelector('.patch-summary');
                const text = summary?.textContent.toLowerCase() || '';
                card.style.display = text.includes('skill balance') ? 'block' : 'none';
            });
        });

        resetBtn.addEventListener('click', () => {
            document.querySelectorAll('.patch-card').forEach(card => {
                card.style.display = 'block';
            });
        });
    }
});
