import { Crepe } from '@milkdown/crepe';
import '@milkdown/crepe/theme/common/style.css';
import '@milkdown/crepe/theme/frame.css';
import './markdown-editor.css';

async function mountEditor(textarea: HTMLTextAreaElement): Promise<void> {
    if (textarea.dataset.markdownEditorReady === '1') {
        return;
    }

    textarea.dataset.markdownEditorReady = '1';

    const host = document.createElement('div');
    host.className = 'markdown-editor-host';
    textarea.insertAdjacentElement('afterend', host);
    textarea.classList.add('markdown-editor-source');

    try {
        const crepe = new Crepe({
            root: host,
            defaultValue: textarea.value,
            features: {
                [Crepe.Feature.TopBar]: true,
                [Crepe.Feature.Toolbar]: true,
                [Crepe.Feature.BlockEdit]: false,
                [Crepe.Feature.ImageBlock]: false,
                [Crepe.Feature.CodeMirror]: false,
                [Crepe.Feature.Table]: false,
                [Crepe.Feature.Latex]: false,
            },
            featureConfigs: {
                [Crepe.Feature.Placeholder]: {
                    text: 'Écrivez ici…',
                },
            },
        });

        await crepe.create();

        const sync = (): void => {
            textarea.value = crepe.getMarkdown();
            textarea.dispatchEvent(new Event('input', { bubbles: true }));
        };

        crepe.on((listener) => {
            listener.markdownUpdated(sync);
        });

        textarea.closest('form')?.addEventListener('submit', sync);
    } catch {
        textarea.classList.remove('markdown-editor-source');
        host.remove();
        delete textarea.dataset.markdownEditorReady;
    }
}

function enhance(): void {
    document.querySelectorAll<HTMLTextAreaElement>('textarea[data-markdown-editor]').forEach((textarea) => {
        void mountEditor(textarea);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', enhance, { once: true });
} else {
    enhance();
}
