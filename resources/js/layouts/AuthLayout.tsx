import AuthLayoutTemplate from '@/layouts/Auth/AuthSimpleLayout';

export default function AuthLayout({
    children,
    title,
    description,
}: {
    children: React.ReactNode;
    title: string;
    description: string;
}) {
    return (
        <AuthLayoutTemplate title={title} description={description}>
            {children}
        </AuthLayoutTemplate>
    );
}
