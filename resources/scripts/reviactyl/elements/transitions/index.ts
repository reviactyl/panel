import { Transition as TransitionComponent } from '@headlessui/react';
import FadeTransition from '@/reviactyl/elements/transitions/FadeTransition';

type TransitionType = typeof TransitionComponent & {
    Fade: typeof FadeTransition;
};

const Transition = Object.assign(TransitionComponent, {
    Fade: FadeTransition,
}) as TransitionType;

export { Transition };
