/**
 * TripShop — Correction automatique en temps réel (LanguageTool)
 * Corrige directement dans le textarea après 800ms d'inactivité
 */
(function () {
    'use strict';

    const API     = 'https://api.languagetool.org/v2/check';
    const DELAY   = 800; // ms après la dernière frappe

    async function check(text, lang) {
        const fd = new FormData();
        fd.append('text', text);
        fd.append('language', lang);
        const r = await fetch(API, { method: 'POST', body: fd });
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return (await r.json()).matches || [];
    }

    function applyAll(text, matches) {
        return [...matches]
            .filter(m => m.replacements?.length)
            .sort((a, b) => b.offset - a.offset)
            .reduce((t, m) =>
                t.slice(0, m.offset) + m.replacements[0].value + t.slice(m.offset + m.length),
                text
            );
    }

    function attach(textareaId, lang) {
        lang = lang || 'fr';

        function doAttach() {
            const ta = document.getElementById(textareaId);
            if (!ta || ta.dataset.scAttached) return;
            ta.dataset.scAttached = '1';

            // Indicateur visuel discret sous le textarea
            const indicator = document.createElement('div');
            indicator.className = 'sc-indicator';
            indicator.style.display = 'none';
            ta.insertAdjacentElement('afterend', indicator);

            let timer = null;
            let lastText = '';

            ta.addEventListener('input', () => {
                clearTimeout(timer);
                const text = ta.value;

                // Pas assez de texte ou pas de changement
                if (text.trim().length < 10) {
                    indicator.style.display = 'none';
                    return;
                }

                // Afficher "en cours..."
                indicator.style.display = 'flex';
                indicator.innerHTML = '<span class="sc-spin"></span> Vérification…';
                indicator.className = 'sc-indicator sc-checking';

                timer = setTimeout(async () => {
                    const current = ta.value;
                    if (current === lastText) {
                        indicator.style.display = 'none';
                        return;
                    }

                    try {
                        const matches = await check(current, lang);

                        // Vérifier que l'utilisateur n'a pas retapé pendant l'appel
                        if (ta.value !== current) return;

                        if (matches.length === 0) {
                            lastText = current;
                            indicator.innerHTML = '✅ Texte correct';
                            indicator.className = 'sc-indicator sc-ok';
                            setTimeout(() => { indicator.style.display = 'none'; }, 2000);
                            return;
                        }

                        // Appliquer les corrections
                        const corrected = applyAll(current, matches);

                        if (corrected !== current) {
                            // Sauvegarder la position du curseur
                            const selStart = ta.selectionStart;
                            const selEnd   = ta.selectionEnd;
                            const diff     = corrected.length - current.length;

                            ta.value = corrected;
                            lastText = corrected;

                            // Restaurer le curseur (ajusté)
                            try {
                                ta.setSelectionRange(selStart + diff, selEnd + diff);
                            } catch {}

                            const nb = matches.filter(m => m.replacements?.length).length;
                            indicator.innerHTML = `✨ ${nb} correction${nb > 1 ? 's' : ''} appliquée${nb > 1 ? 's' : ''}`;
                            indicator.className = 'sc-indicator sc-done';
                        } else {
                            lastText = current;
                            indicator.innerHTML = '✅ Texte correct';
                            indicator.className = 'sc-indicator sc-ok';
                        }

                        setTimeout(() => { indicator.style.display = 'none'; }, 2500);

                    } catch {
                        indicator.style.display = 'none';
                    }
                }, DELAY);
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', doAttach);
        } else {
            doAttach();
        }
    }

    window.SpellCheck = { attach };

})();
