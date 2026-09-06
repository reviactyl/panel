import React, { forwardRef } from 'react';
import { Form } from 'formik';
import FlashMessageRender from '@/components/FlashMessageRender';
import Card from '@/reviactyl/ui/Card';
import Title from '@/reviactyl/ui/Title';
import { LogoContainer } from '@/reviactyl/ui/LogoContainer';
import { ApplicationStore } from '@/state';
import { useStoreState } from 'easy-peasy';
import Footer from '@/reviactyl/ui/Footer';
import { ExtensionSlot } from '@/extensions/ExtensionSlot';
import NavbarLanguageSwitcher from '@/reviactyl/ui/NavbarLanguageSwitcher';

type Props = React.DetailedHTMLProps<React.FormHTMLAttributes<HTMLFormElement>, HTMLFormElement> & {
    title?: string;
};

export default forwardRef<HTMLFormElement, Props>(({ title, ...props }, ref) => {
    const logo = useStoreState((state: ApplicationStore) => state.settings.data!.logo);
    const name = useStoreState((state: ApplicationStore) => state.settings.data!.name);
    return (
        <div className='mx-auto my-auto'>
            <ExtensionSlot name={'auth:form:above'} />
            <Form {...props} ref={ref}>
                <div className='w-screen max-w-[28.125rem] p-5'>
                    <FlashMessageRender className='mb-2' />
                    <ExtensionSlot name={'auth:card:before'} />
                    <div className='mb-3 flex justify-end'>
                        <NavbarLanguageSwitcher />
                    </div>
                    <LogoContainer>
                        <img src={logo} alt={name} className='h-[3rem]' />
                    </LogoContainer>
                    <Card>
                        {title && <Title className='text-3xl text-center pb-3'>{title}</Title>}
                        {props.children}
                    </Card>
                    <ExtensionSlot name={'auth:form:after'} />
                </div>
            </Form>
            <ExtensionSlot name={'auth:form:below'} />
            <Footer />
        </div>
    );
});
