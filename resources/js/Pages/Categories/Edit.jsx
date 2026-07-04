import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import CategoryForm from './Partials/CategoryForm';
import { Head, useForm } from '@inertiajs/react';

export default function Edit({ category }) {
    // Đổ dữ liệu cũ của category vào form
    const { data, setData, put, errors, processing } = useForm({
        name: category.name || '',
        status: category.status || '1',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        put(route('categories.update', category.id)); // Gọi hàm update trong Controller
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Chỉnh sửa danh mục: {category.name}</h2>}
        >
            <Head title="Sửa Danh Mục" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
                        <CategoryForm
                            data={data}
                            setData={setData}
                            errors={errors}
                            processing={processing}
                            submitAction={handleSubmit}
                            buttonText="Cập nhật"
                        />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}