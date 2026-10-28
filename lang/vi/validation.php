<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines (Vietnamese)
    |--------------------------------------------------------------------------
    |
    | Các thông báo lỗi xác thực dùng chung cho toàn bộ ứng dụng.
    |
    */

    'accepted' => 'Trường :attribute phải được chấp nhận.',
    'accepted_if' => 'Trường :attribute phải được chấp nhận khi :other là :value.',
    'active_url' => 'Trường :attribute phải là một URL hợp lệ.',
    'after' => 'Trường :attribute phải là một ngày sau ngày :date.',
    'after_or_equal' => 'Trường :attribute phải là một ngày sau hoặc bằng ngày :date.',
    'alpha' => 'Trường :attribute chỉ được chứa chữ cái.',
    'alpha_dash' => 'Trường :attribute chỉ được chứa chữ cái, chữ số, dấu gạch ngang và gạch dưới.',
    'alpha_num' => 'Trường :attribute chỉ được chứa chữ cái và chữ số.',
    'any_of' => 'Trường :attribute không hợp lệ.',
    'array' => 'Trường :attribute phải là một mảng.',
    'ascii' => 'Trường :attribute chỉ được chứa ký tự chữ và số đơn byte.',
    'before' => 'Trường :attribute phải là một ngày trước ngày :date.',
    'before_or_equal' => 'Trường :attribute phải là một ngày trước hoặc bằng ngày :date.',
    'between' => [
        'array' => 'Trường :attribute phải có số phần tử từ :min đến :max.',
        'file' => 'Trường :attribute phải có dung lượng từ :min đến :max KB.',
        'numeric' => 'Trường :attribute phải có giá trị từ :min đến :max.',
        'string' => 'Trường :attribute phải có độ dài từ :min đến :max ký tự.',
    ],
    'boolean' => 'Trường :attribute phải là true hoặc false.',
    'can' => 'Trường :attribute chứa giá trị không được phép.',
    'confirmed' => 'Giá trị xác nhận của :attribute không khớp.',
    'contains' => 'Trường :attribute còn thiếu một giá trị bắt buộc.',
    'current_password' => 'Mật khẩu không đúng.',
    'date' => 'Trường :attribute phải là một ngày hợp lệ.',
    'date_equals' => 'Trường :attribute phải là một ngày bằng ngày :date.',
    'date_format' => 'Trường :attribute phải có định dạng :format.',
    'decimal' => 'Trường :attribute phải có :decimal chữ số thập phân.',
    'declined' => 'Trường :attribute phải bị từ chối.',
    'declined_if' => 'Trường :attribute phải bị từ chối khi :other là :value.',
    'different' => 'Trường :attribute và :other phải khác nhau.',
    'digits' => 'Trường :attribute phải có :digits chữ số.',
    'digits_between' => 'Trường :attribute phải có số chữ số từ :min đến :max.',
    'dimensions' => 'Trường :attribute có kích thước hình ảnh không hợp lệ.',
    'distinct' => 'Trường :attribute có giá trị bị trùng lặp.',
    'doesnt_contain' => 'Trường :attribute không được chứa bất kỳ giá trị nào sau đây: :values.',
    'doesnt_end_with' => 'Trường :attribute không được kết thúc bằng một trong các giá trị sau: :values.',
    'doesnt_start_with' => 'Trường :attribute không được bắt đầu bằng một trong các giá trị sau: :values.',
    'email' => 'Trường :attribute phải là một địa chỉ email hợp lệ.',
    'encoding' => 'Trường :attribute phải được mã hóa bằng :encoding.',
    'ends_with' => 'Trường :attribute phải kết thúc bằng một trong các giá trị sau: :values.',
    'enum' => ':attribute được chọn không hợp lệ.',
    'exists' => ':attribute được chọn không hợp lệ.',
    'extensions' => 'Trường :attribute phải có một trong các phần mở rộng sau: :values.',
    'file' => 'Trường :attribute phải là một tệp tin.',
    'filled' => 'Trường :attribute không được để trống.',
    'gt' => [
        'array' => 'Trường :attribute phải có nhiều hơn :value phần tử.',
        'file' => 'Trường :attribute phải có dung lượng lớn hơn :value KB.',
        'numeric' => 'Trường :attribute phải lớn hơn :value.',
        'string' => 'Trường :attribute phải có nhiều hơn :value ký tự.',
    ],
    'gte' => [
        'array' => 'Trường :attribute phải có từ :value phần tử trở lên.',
        'file' => 'Trường :attribute phải có dung lượng từ :value KB trở lên.',
        'numeric' => 'Trường :attribute phải lớn hơn hoặc bằng :value.',
        'string' => 'Trường :attribute phải có từ :value ký tự trở lên.',
    ],
    'hex_color' => 'Trường :attribute phải là một mã màu hex hợp lệ.',
    'image' => 'Trường :attribute phải là một hình ảnh.',
    'in' => ':attribute được chọn không hợp lệ.',
    'in_array' => 'Trường :attribute phải tồn tại trong :other.',
    'in_array_keys' => 'Trường :attribute phải chứa ít nhất một trong các khóa sau: :values.',
    'integer' => 'Trường :attribute phải là số nguyên.',
    'ip' => 'Trường :attribute phải là một địa chỉ IP hợp lệ.',
    'ipv4' => 'Trường :attribute phải là một địa chỉ IPv4 hợp lệ.',
    'ipv6' => 'Trường :attribute phải là một địa chỉ IPv6 hợp lệ.',
    'json' => 'Trường :attribute phải là một chuỗi JSON hợp lệ.',
    'list' => 'Trường :attribute phải là một danh sách.',
    'lowercase' => 'Trường :attribute phải viết thường.',
    'lt' => [
        'array' => 'Trường :attribute phải có ít hơn :value phần tử.',
        'file' => 'Trường :attribute phải có dung lượng nhỏ hơn :value KB.',
        'numeric' => 'Trường :attribute phải nhỏ hơn :value.',
        'string' => 'Trường :attribute phải có ít hơn :value ký tự.',
    ],
    'lte' => [
        'array' => 'Trường :attribute không được có nhiều hơn :value phần tử.',
        'file' => 'Trường :attribute phải có dung lượng không quá :value KB.',
        'numeric' => 'Trường :attribute phải nhỏ hơn hoặc bằng :value.',
        'string' => 'Trường :attribute không được có nhiều hơn :value ký tự.',
    ],
    'mac_address' => 'Trường :attribute phải là một địa chỉ MAC hợp lệ.',
    'max' => [
        'array' => 'Trường :attribute không được có nhiều hơn :max phần tử.',
        'file' => 'Trường :attribute không được lớn hơn :max KB.',
        'numeric' => 'Trường :attribute không được lớn hơn :max.',
        'string' => 'Trường :attribute không được dài hơn :max ký tự.',
    ],
    'max_digits' => 'Trường :attribute không được có nhiều hơn :max chữ số.',
    'mimes' => 'Trường :attribute phải là tệp có định dạng: :values.',
    'mimetypes' => 'Trường :attribute phải là tệp có định dạng: :values.',
    'min' => [
        'array' => 'Trường :attribute phải có ít nhất :min phần tử.',
        'file' => 'Trường :attribute phải có dung lượng ít nhất :min KB.',
        'numeric' => 'Trường :attribute phải có giá trị ít nhất :min.',
        'string' => 'Trường :attribute phải có ít nhất :min ký tự.',
    ],
    'min_digits' => 'Trường :attribute phải có ít nhất :min chữ số.',
    'missing' => 'Trường :attribute phải không tồn tại.',
    'missing_if' => 'Trường :attribute phải không tồn tại khi :other là :value.',
    'missing_unless' => 'Trường :attribute phải không tồn tại trừ khi :other là :value.',
    'missing_with' => 'Trường :attribute phải không tồn tại khi :values hiện diện.',
    'missing_with_all' => 'Trường :attribute phải không tồn tại khi :values cùng hiện diện.',
    'multiple_of' => 'Trường :attribute phải là bội số của :value.',
    'not_in' => ':attribute được chọn không hợp lệ.',
    'not_regex' => 'Định dạng của trường :attribute không hợp lệ.',
    'numeric' => 'Trường :attribute phải là số.',
    'password' => [
        'letters' => 'Trường :attribute phải chứa ít nhất một chữ cái.',
        'mixed' => 'Trường :attribute phải chứa ít nhất một chữ hoa và một chữ thường.',
        'numbers' => 'Trường :attribute phải chứa ít nhất một chữ số.',
        'symbols' => 'Trường :attribute phải chứa ít nhất một ký tự đặc biệt.',
        'uncompromised' => ':attribute đã bị rò rỉ trong một vụ vi phạm dữ liệu. Vui lòng chọn :attribute khác.',
    ],
    'present' => 'Trường :attribute phải hiện diện.',
    'present_if' => 'Trường :attribute phải hiện diện khi :other là :value.',
    'present_unless' => 'Trường :attribute phải hiện diện trừ khi :other là :value.',
    'present_with' => 'Trường :attribute phải hiện diện khi :values hiện diện.',
    'present_with_all' => 'Trường :attribute phải hiện diện khi :values cùng hiện diện.',
    'prohibited' => 'Trường :attribute bị cấm.',
    'prohibited_if' => 'Trường :attribute bị cấm khi :other là :value.',
    'prohibited_if_accepted' => 'Trường :attribute bị cấm khi :other được chấp nhận.',
    'prohibited_if_declined' => 'Trường :attribute bị cấm khi :other bị từ chối.',
    'prohibited_unless' => 'Trường :attribute bị cấm trừ khi :other là :value.',
    'prohibits' => 'Trường :attribute cấm :other hiện diện.',
    'regex' => 'Định dạng của trường :attribute không hợp lệ.',
    'required' => 'Trường :attribute không được để trống.',
    'required_array_keys' => 'Trường :attribute phải chứa các mục sau: :values.',
    'required_if' => 'Trường :attribute không được để trống khi :other là :value.',
    'required_if_accepted' => 'Trường :attribute không được để trống khi :other được chấp nhận.',
    'required_if_declined' => 'Trường :attribute không được để trống khi :other bị từ chối.',
    'required_unless' => 'Trường :attribute không được để trống trừ khi :other là :value.',
    'required_with' => 'Trường :attribute không được để trống khi :values hiện diện.',
    'required_with_all' => 'Trường :attribute không được để trống khi :values cùng hiện diện.',
    'required_without' => 'Trường :attribute không được để trống khi :values không hiện diện.',
    'required_without_all' => 'Trường :attribute không được để trống khi không có :values nào hiện diện.',
    'same' => 'Trường :attribute phải khớp với :other.',
    'size' => [
        'array' => 'Trường :attribute phải chứa :size phần tử.',
        'file' => 'Trường :attribute phải có dung lượng :size KB.',
        'numeric' => 'Trường :attribute phải bằng :size.',
        'string' => 'Trường :attribute phải có :size ký tự.',
    ],
    'starts_with' => 'Trường :attribute phải bắt đầu bằng một trong các giá trị sau: :values.',
    'string' => 'Trường :attribute phải là một chuỗi ký tự.',
    'timezone' => 'Trường :attribute phải là một múi giờ hợp lệ.',
    'ulid' => 'Trường :attribute phải là một ULID hợp lệ.',
    'unique' => 'Giá trị của trường :attribute đã tồn tại.',
    'uploaded' => 'Tải lên trường :attribute thất bại.',
    'uppercase' => 'Trường :attribute phải viết hoa.',
    'url' => 'Trường :attribute phải là một URL hợp lệ.',
    'uuid' => 'Trường :attribute phải là một UUID hợp lệ.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        'name' => 'tên',
        'email' => 'email',
        'password' => 'mật khẩu',
        'password_confirmation' => 'xác nhận mật khẩu',
        'current_password' => 'mật khẩu hiện tại',
        'input_content' => 'nội dung',
        'prompt_template_id' => 'mẫu prompt',
        'target_platform' => 'nền tảng đích',
        'system_instruction' => 'system instruction',
        'sample_input' => 'nội dung mẫu',
        'instruction' => 'yêu cầu chỉnh sửa',
        'dialogue' => 'lời thoại',
        'voice_id' => 'giọng đọc',
        'image_prompt' => 'prompt hình ảnh',
        'category' => 'danh mục',
    ],

];
