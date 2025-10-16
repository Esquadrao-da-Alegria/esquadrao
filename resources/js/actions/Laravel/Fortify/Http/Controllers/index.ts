import ConfirmablePasswordController from './ConfirmablePasswordController'
import ConfirmedPasswordStatusController from './ConfirmedPasswordStatusController'
import TwoFactorAuthenticatedSessionController from './TwoFactorAuthenticatedSessionController'
import TwoFactorAuthenticationController from './TwoFactorAuthenticationController'
import ConfirmedTwoFactorAuthenticationController from './ConfirmedTwoFactorAuthenticationController'
import TwoFactorQrCodeController from './TwoFactorQrCodeController'
import TwoFactorSecretKeyController from './TwoFactorSecretKeyController'
import RecoveryCodeController from './RecoveryCodeController'

const Controllers = {
    ConfirmablePasswordController: Object.assign(ConfirmablePasswordController, ConfirmablePasswordController),
    ConfirmedPasswordStatusController: Object.assign(ConfirmedPasswordStatusController, ConfirmedPasswordStatusController),
    TwoFactorAuthenticatedSessionController: Object.assign(TwoFactorAuthenticatedSessionController, TwoFactorAuthenticatedSessionController),
    TwoFactorAuthenticationController: Object.assign(TwoFactorAuthenticationController, TwoFactorAuthenticationController),
    ConfirmedTwoFactorAuthenticationController: Object.assign(ConfirmedTwoFactorAuthenticationController, ConfirmedTwoFactorAuthenticationController),
    TwoFactorQrCodeController: Object.assign(TwoFactorQrCodeController, TwoFactorQrCodeController),
    TwoFactorSecretKeyController: Object.assign(TwoFactorSecretKeyController, TwoFactorSecretKeyController),
    RecoveryCodeController: Object.assign(RecoveryCodeController, RecoveryCodeController),
}

export default Controllers