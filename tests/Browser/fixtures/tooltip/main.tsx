import React from 'react';
import { createRoot } from 'react-dom/client';
import Tooltip from '@/reviactyl/elements/tooltip/Tooltip';

const params = new URLSearchParams(location.search);
const placement = (params.get('placement') || 'top') as React.ComponentProps<typeof Tooltip>['placement'];
const edge = params.has('edge');
createRoot(document.getElementById('root')!).render(
    <div
        style={{
            position: 'fixed',
            left: edge && placement?.startsWith('left') ? 2 : '50%',
            top: edge && placement?.startsWith('top') ? 2 : '50%',
        }}
    >
        <Tooltip
            arrow={!params.has('no-arrow')}
            placement={placement}
            rest={0}
            interactions={params.has('click') ? ['click'] : ['hover', 'focus']}
            disabled={params.has('disabled')}
            className={params.has('custom') ? 'custom-tooltip' : undefined}
            content='Tooltip details'
        >
            <button
                onClick={(event) => {
                    event.currentTarget.textContent = 'Activated';
                }}
            >
                Show details
            </button>
        </Tooltip>
    </div>,
);
