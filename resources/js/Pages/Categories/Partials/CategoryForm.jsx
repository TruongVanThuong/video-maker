import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import PrimaryButton from '@/Components/PrimaryButton';
import { Link } from '@inertiajs/react';

export default function CategoryForm({ data, setData, errors, processing, submitAction, buttonText }) {
    return (
        <form onSubmit={submitAction} className="space-y-6 max-w-xl">
            {/* Trường Name */}
            <div>
                <InputLabel htmlFor="name" value="Tên danh mục" />
                <TextInput
                    id="name"
                    type="text"
                    name="name"
                    value={data.name}
                    className="mt-1 block w-full"
                    isFocused={true}
                    onChange={(e) => setData('name', e.target.value)}
                />
                <InputError message={errors.name} className="mt-2" />
            </div>

            {/* Trường Status */}
            <div>
                <InputLabel htmlFor="status" value="Trạng thái" />
                <select
                    id="status"
                    name="status"
                    value={data.status}
                    className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    onChange={(e) => setData('status', e.target.value)}
                >
                    <option value="1">Hiển thị (Active)</option>
                    <option value="0">Ẩn (Inactive)</option>
                </select>
                <InputError message={errors.status} className="mt-2" />
            </div>

            {/* Nút bấm */}
            <div className="flex items-center gap-4">
                <PrimaryButton disabled={processing}>{buttonText}</PrimaryButton>
                <Link
                    href={route('categories')}
                    className="text-sm text-gray-600 hover:text-gray-900 underline"
                >
                    Hủy bỏ
                </Link>
            </div>
        </form>
    );
}