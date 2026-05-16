import { Route, Routes, useNavigate } from 'react-router-dom';
import LoginContainer from '@/components/auth/LoginContainer';
import ForgotPasswordContainer from '@/components/auth/ForgotPasswordContainer';
import ResetPasswordContainer from '@/components/auth/ResetPasswordContainer';
import LoginCheckpointContainer from '@/components/auth/LoginCheckpointContainer';
import RegisterContainer from '@/components/auth/RegisterContainer';
import { NotFound } from '@/reviactyl/elements/ScreenBlock';
import { Navigate } from 'react-router-dom';
import { useStoreState } from 'easy-peasy';
import styled from 'styled-components';
import tw from 'twin.macro';

const RouterContainer = styled.div`
    ${tw`flex flex-col min-h-screen h-full`}
`;

export default () => {
    const navigate = useNavigate();
    const registrationEnabled = useStoreState((state) => state.settings.data?.registrationEnabled ?? true);

    return (
        <RouterContainer>
            <Routes>
                <Route path='/login' element={<LoginContainer />} />
                <Route
                    path='/register'
                    element={registrationEnabled ? <RegisterContainer /> : <Navigate to='/auth/login' replace />}
                />
                <Route path='/login/checkpoint' element={<LoginCheckpointContainer />} />
                <Route path='/password' element={<ForgotPasswordContainer />} />
                <Route path='/password/reset/:token' element={<ResetPasswordContainer />} />
                <Route path='/checkpoint' />
                <Route path='*' element={<NotFound onBack={() => navigate('/auth/login')} />} />
            </Routes>
        </RouterContainer>
    );
};
