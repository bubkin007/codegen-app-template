'use client'

import { Button, Paper, Stack } from '@mantine/core'
import { useForm } from '@mantine/form'
import { LogIn } from 'lucide-react'
import { useRouter } from 'next/navigation'
import { useTranslations } from 'next-intl'

import styles from './styles.module.scss'

import { toDashboard } from '../../helpers/navigation'
import { useLoginMutation } from '../../store/api'

import { EmailInput } from '@modules/shared/components/email-input'
import { PasswordInput } from '@modules/shared/components/password-input'
import { isValidEmail } from '@modules/shared/helpers/validation'
import type { IApiErrorResponse } from '@modules/shared/models/api-error-response.interface'

type LoginFormValues = {
    email: string
    password: string
}

const initialValues: LoginFormValues = {
    email: '',
    password: '',
}

export function LoginForm() {
    const t = useTranslations('Auth.LoginForm')
    const sharedT = useTranslations('Shared.Validation')
    const router = useRouter()
    const [login, { isLoading: isSubmitting, reset: resetLogin }] = useLoginMutation()
    const form = useForm<LoginFormValues>({
        initialValues,
        validate: {
            email: (value) => {
                if (value.trim().length === 0) {
                    return sharedT('requiredField')
                }

                return isValidEmail(value) ? null : sharedT('invalidEmail')
            },
            password: (value) => (value.length > 0 ? null : sharedT('requiredField')),
        },
        transformValues: (values) => ({
            email: values.email.trim(),
            password: values.password,
        }),
    })

    const handleSubmit = async (values: LoginFormValues) => {
        form.clearErrors()
        resetLogin()

        try {
            await login(values).unwrap()
            toDashboard(router)
        } catch (error) {
            const apiErrors = (error as IApiErrorResponse).errors
            if (apiErrors && Object.keys(apiErrors).length > 0) {
                return
            }

            const errorMessage = t('invalidCredentials')
            form.setFieldError('email', errorMessage)
        }
    }

    return (
        <main className={styles.login}>
            <Paper
                noValidate
                component="form"
                className={styles.formCard}
                radius="sm"
                onSubmit={form.onSubmit(handleSubmit)}
            >
                <Stack gap="lg">
                    <Stack gap="md">
                        <EmailInput required {...form.getInputProps('email')} />

                        <PasswordInput required {...form.getInputProps('password')} />
                    </Stack>

                    <Button
                        type="submit"
                        color="teal"
                        fullWidth
                        loading={isSubmitting}
                        leftSection={<LogIn size={18} strokeWidth={1.9} />}
                    >
                        {t('submit')}
                    </Button>
                </Stack>
            </Paper>
        </main>
    )
}
