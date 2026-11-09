import { Fragment } from 'react';
import { ExtensionModule } from '@/extensions/ExtensionModule';
import { useExtensions } from '@/extensions/useExtensions';
import type { ExtensionSlotDefinition } from '@/extensions/types';

interface SlotContext {
    eggId?: number;
    nestId?: number;
}

interface Props {
    name: string;
    context?: SlotContext;
}

const isAllowedByGuard = (slot: ExtensionSlotDefinition, context?: SlotContext): boolean => {
    const eggId = context?.eggId;
    const nestId = context?.nestId;
    const slotEggId = slot.eggId ?? slot.egg_id;
    const slotEggIds = slot.eggIds ?? slot.egg_ids;

    const hasGuards =
        slotEggId !== undefined ||
        slot.nest_id !== undefined ||
        Array.isArray(slotEggIds) ||
        Array.isArray(slot.nest_ids);

    if (!hasGuards) {
        return true;
    }

    if (slotEggId !== undefined && slotEggId === eggId) {
        return true;
    }

    if (slot.nest_id !== undefined && slot.nest_id === nestId) {
        return true;
    }

    if (Array.isArray(slotEggIds) && eggId !== undefined && slotEggIds.includes(eggId)) {
        return true;
    }

    if (Array.isArray(slot.nest_ids) && nestId !== undefined && slot.nest_ids.includes(nestId)) {
        return true;
    }

    return false;
};

export const ExtensionSlot = ({ name, context }: Props) => {
    const { data } = useExtensions();
    const extensions = Array.isArray(data) ? data : [];

    const renderedSlots = extensions
        .flatMap((extension) =>
            (extension.frontend?.slots ?? [])
                .filter((slot) => slot.name === name)
                .filter((slot) => isAllowedByGuard(slot, context))
                .map((slot) => ({
                    extensionId: extension.id,
                    slot,
                })),
        )
        .sort((a, b) => (a.slot.order ?? 0) - (b.slot.order ?? 0));

    if (renderedSlots.length === 0) {
        return null;
    }

    return (
        <Fragment>
            {renderedSlots.map(({ extensionId, slot }, index) => (
                <ExtensionModule
                    key={`${extensionId}:${slot.name}:${slot.module}:${index}`}
                    extensionId={extensionId}
                    modulePath={slot.module}
                    exportName={slot.export}
                />
            ))}
        </Fragment>
    );
};
