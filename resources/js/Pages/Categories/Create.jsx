import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import CategoryForm from './Partials/CategoryForm';
import { Head, useForm } from '@inertiajs/react';

export default function Create() {
    const { data, setData, post, errors, processing } = useForm({
        name: '',
        status: '1', // Mặc định chọn hiển thị
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('categories.store')); // Gọi sang hàm store trong Controller
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Thêm Danh Mục Mới</h2>}
        >
            <Head title="Thêm Danh Mục" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
                        <CategoryForm
                            data={data}
                            setData={setData}
                            errors={errors}
                            processing={processing}
                            submitAction={handleSubmit}
                            buttonText="Tạo mới"
                        />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}