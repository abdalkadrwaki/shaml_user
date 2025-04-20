// app.js (في الأعلى)
import $ from 'jquery';
window.$ = window.jQuery = $;  // الآن jQuery جاهز للعالمية

// بعد ذلك استورد كل المكوّنات المرتبطة به
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import 'select2/dist/css/select2.min.css';
import 'select2/dist/js/select2.min.js';  // سيجد الآن window.jQuery
import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';
import 'datatables.net-bs5';
import Swal from 'sweetalert2';
// … بقية الكود

let table = new DataTable('.myTable');




// حدث مخصص لإعادة التهيئة على المحتوى الديناميكي

// إضافة الحدث لجميع الحقول التي تحتوي على الكلاس 'number-only'
document.querySelectorAll('.number-only').forEach(function (input) {
    input.addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9.]/g, '');
    });
});


// عند تغيير الخيار في الـ Select
$('#destination_transfer').change(function () {
    var destinationId = $(this).val();
    console.log(destinationId);  // إضافة هذا السطر للتحقق من القيمة

    if (destinationId && destinationId !== '#') {  // تحقق من القيمة بشكل صحيح
        $.ajax({
            url: '/get-destination-address',
            method: 'GET',
            data: { destination_id: destinationId },
            success: function (response) {
                if (response.address) {
                    $('#destination_address').text(response.address);
                    $('#destination_address_container').show();
                } else {
                    $('#destination_address').text('لم يتم العثور على العنوان.');
                    $('#destination_address_container').show();
                }
            },
            error: function () {
                alert('حدث خطأ في جلب العنوان. يرجى المحاولة لاحقًا.');
            }
        });
    } else {
        $('#destination_address_container').hide();
    }
});

$(document).ready(function () {
    $('#destination_syp').on('change', function () {
        var destinationId = $(this).val();
        console.log("Destination ID: ", destinationId);  // تحقق من الـ ID

        if (destinationId) {
            $.ajax({
                url: '/get-exchange-rate',
                method: 'GET',
                data: { destination_id: destinationId },
                success: function (response) {
                    if (response.success) {
                        $('#exchange_rate_syp').val(response.exchange_rate);
                    } else {
                        $('#exchange_rate_syp').val('');
                        alert(response.message);
                    }
                },
                error: function () {
                    alert('حدث خطأ أثناء جلب البيانات');
                }
            });
        }
    });
});

$('#destination_syp').change(function () {
    var destinationId = $(this).val();
    console.log(destinationId);  // إضافة هذا السطر للتحقق من القيمة

    if (destinationId && destinationId !== '#') {  // تحقق من القيمة بشكل صحيح
        $.ajax({
            url: '/get-destination-address',
            method: 'GET',
            data: { destination_id: destinationId },
            success: function (response) {
                if (response.address) {
                    $('#destination_address_syp').text(response.address);
                    $('#destination_address_container_syp').show();
                } else {
                    $('#destination_address_syp').text('لم يتم العثور على العنوان.');
                    $('#destination_address_container_syp').show();
                }
            },
            error: function () {
                alert('حدث خطأ في جلب العنوان. يرجى المحاولة لاحقًا.');
            }
        });
    } else {
        $('#destination_address_container_syp').hide();
    }
});

$(document).ready(function () {
    // عند الضغط على زر تعديل المبلغ
    $('.update-btn').click(function () {
        var button = $(this);
        var input = button.prev('.limited-input'); // حقل الإدخال السابق
        var limitedValue = input.val(); // قيمة المبلغ المدخل
        var requestId = button.data('id'); // ID الطلب

        // التحقق من صحة القيمة المدخلة
        if (!limitedValue || isNaN(limitedValue) || limitedValue < 0) {
            Swal.fire({
                title: 'خطأ!',
                text: 'يرجى إدخال مبلغ صحيح.',
                icon: 'error',
                confirmButtonText: 'حسناً'
            });
            return;
        }

        // عرض رسالة التأكيد قبل المتابعة
        Swal.fire({
            title: 'هل أنت متأكد؟',
            text: "سيتم تعديل المبلغ!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'نعم، قم بالتعديل!',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                // الحصول على CSRF Token من الـ meta
                var csrfToken = $('meta[name="csrf-token"]').attr('content');

                // إرسال الطلب عبر AJAX
                $.ajax({
                    url: '/update-limited/' + requestId,
                    type: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,  // استخدام Token من الـ meta
                    },
                    data: {
                        limited: limitedValue,  // قيمة المبلغ
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'تم التعديل بنجاح!',
                                text: 'تم تعديل المبلغ بنجاح!',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            });
                            // تحديث قيمة المبلغ في الواجهة
                            input.val(limitedValue);
                        } else {
                            Swal.fire({
                                title: 'خطأ!',
                                text: 'حدث خطأ، يرجى المحاولة مرة أخرى.',
                                icon: 'error',
                                confirmButtonText: 'حسناً'
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            title: 'خطأ في الاتصال!',
                            text: 'حدث خطأ في الاتصال.',
                            icon: 'error',
                            confirmButtonText: 'حسناً'
                        });
                    }
                });
            }
        });
    });

    // عند الضغط على زر تحديث كلمة المرور
    $('.update-password-btn').click(function () {
        var button = $(this);
        var input = button.prev('.password-input'); // الحقل المرتبط
        var passwordValue = input.val(); // قيمة كلمة المرور المدخلة
        var requestId = button.data('id'); // ID الطلب

        // التحقق من صحة كلمة المرور
        if (!passwordValue || passwordValue.length < 6) {
            Swal.fire({
                title: 'خطأ!',
                text: 'يجب أن تحتوي كلمة المرور على 6 أحرف على الأقل.',
                icon: 'error',
                confirmButtonText: 'حسناً'
            });
            return;
        }

        // عرض رسالة التأكيد قبل المتابعة
        Swal.fire({
            title: 'هل أنت متأكد؟',
            text: "سيتم تحديث كلمة المرور!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'نعم، قم بالتحديث!',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                // الحصول على CSRF Token من الـ meta
                var csrfToken = $('meta[name="csrf-token"]').attr('content');

                // إرسال الطلب عبر AJAX
                $.ajax({
                    url: '/update-password/' + requestId,
                    type: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    data: {
                        password: passwordValue, // إرسال كلمة المرور الجديدة
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'تم التحديث بنجاح!',
                                text: 'تم تحديث كلمة المرور بنجاح!',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            });
                            // تحديث قيمة الحقل يدويًا بعد التحديث
                            input.attr('value', passwordValue);
                        } else {
                            Swal.fire({
                                title: 'خطأ!',
                                text: 'حدث خطأ، يرجى المحاولة مرة أخرى.',
                                icon: 'error',
                                confirmButtonText: 'حسناً'
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            title: 'خطأ في الاتصال!',
                            text: 'حدث خطأ في الاتصال.',
                            icon: 'error',
                            confirmButtonText: 'حسناً'
                        });
                    }
                });
            }
        });
    });
});

$(document).ready(function () {
    // عند الضغط على زر التفعيل/الإلغاء
    $('.toggle-stop-btn').click(function () {
        var button = $(this);
        // استخراج الحقل سواء من data-field أو data-field2
        var field = button.data('field') || button.data('field2');
        var requestId = button.data('id'); // رقم الطلب
        var csrfToken = $('meta[name="csrf-token"]').attr('content');

        // عرض رسالة التأكيد قبل المتابعة
        Swal.fire({
            title: 'هل أنت متأكد؟',
            text: "سيتم تحديث الحالة!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'نعم، حدث التحديث!',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                // إرسال الطلب عبر AJAX بعد التأكيد
                $.ajax({
                    url: '/toggle-stop-movements/' + requestId,
                    type: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    data: {
                        field: field // إرسال الحقل المراد تغييره
                    },
                    success: function (response) {
                        if (response.success) {
                            // عرض رسالة النجاح باستخدام SweetAlert2
                            Swal.fire({
                                title: 'تم التحديث بنجاح!',
                                text: 'تم تحديث الحالة بنجاح.',
                                icon: 'success',
                                timer: 1500,
                                timerProgressBar: true,
                                showConfirmButton: false,
                                width: '300px',
                                padding: '10px',
                                backdrop: 'rgba(0,0,0,0.4)',
                                customClass: {
                                    popup: 'animated bounceIn'
                                }
                            });

                            // تحديد النص الجديد بناءً على نوع الحقل
                            var newText;
                            if (field === 'Slice_type_1' || field === 'Slice_type_2') {
                                newText = response[field] ? 'الشريحة الثانية' : 'الشريحة الاولى';
                            } else {
                                newText = response[field] ? ' مفعل ' : 'غير مفعل';
                            }
                            button.text(newText);

                            // تحديث لون الزر بناءً على القيمة الجديدة
                            if (response[field]) {
                                button.removeClass('bg-red-500 hover:bg-red-600 focus:ring-red-600')
                                    .addClass('bg-green-500 hover:bg-green-600 focus:ring-green-600');
                            } else {
                                button.removeClass('bg-green-500 hover:bg-green-600 focus:ring-green-600')
                                    .addClass('bg-red-500 hover:bg-red-600 focus:ring-red-600');
                            }
                        } else {
                            // في حالة عدم نجاح العملية
                            Swal.fire({
                                title: 'خطأ!',
                                text: 'حدث خطأ، يرجى المحاولة مرة أخرى.',
                                icon: 'error',
                                timer: 1500,
                                timerProgressBar: true,
                                showConfirmButton: false,
                                width: '300px',
                                padding: '10px',
                                backdrop: 'rgba(0,0,0,0.4)',
                                customClass: {
                                    popup: 'animated shake'
                                }
                            });
                        }
                    },
                    error: function () {
                        // في حالة حدوث خطأ في الاتصال بالخادم
                        Swal.fire({
                            title: 'خطأ في الاتصال!',
                            text: 'حدث خطأ في الاتصال بالخادم.',
                            icon: 'error',
                            timer: 1000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                            width: '300px',
                            padding: '10px',
                            backdrop: 'rgba(0,0,0,0.4)',
                            customClass: {
                                popup: 'animated shake'
                            }
                        });
                    }
                });
            }
        });
    });
});

$(document).ready(function() {
    $('.js-example-basic-single').select2();
});

/*
function detectDevTools() {
    const devToolsOpened = () => {
        const threshold = 160; // الحد الأدنى للعرض عند فتح لوحة المطور
        const widthThreshold = window.outerWidth - window.innerWidth > threshold;
        const heightThreshold = window.outerHeight - window.innerHeight > threshold;
        return widthThreshold || heightThreshold;
    };

    // التحقق كل ثانية
    setInterval(() => {
        if (devToolsOpened()) {
            console.log('لوحة المطور مفتوحة!');
            // إرسال طلب لتسجيل الخروج
            fetch('/logout', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
            }).then(response => {
                if (response.ok) {
                    window.location.reload(); // إعادة تحميل الصفحة بعد تسجيل الخروج
                }
            });
        }
    }, 1000);
}

// تشغيل الدالة عند تحميل الصفحة
window.onload = detectDevTools;


// تشغيل الدالة عند تحميل الصفحة
window.onload = detectDevTools;
*/
