// ==============================
// الواردات/imports
// ==============================

import $ from 'jquery';
window.$ = window.jQuery = $; // جعل jQuery متاحًا عالميًا

import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js'; // شامل Popper.js

import 'select2/dist/css/select2.min.css';
import 'select2/dist/js/select2.min.js';

import DataTable from 'datatables.net-dt';
import 'datatables.net-bs5'; // التكامل مع Bootstrap 5

import Swal from 'sweetalert2';
window.Swal = Swal;

import './bootstrap'; // إعدادات Laravel الإضافية (إن وجدت)


// ==============================
// الوظائف المساعدة/helpers
// ==============================

/**
 * تجلب العنوان الخاص بالوجهة وتعرضه أو تخفي الحاوية
 * @param {string} selectSel - مُحدِّد عنصر الـ <select>
 * @param {string} addrSel   - مُحدِّد عنصر عرض العنوان
 * @param {string} contSel   - مُحدِّد حاوية العنصر
 * @param {string} url       - رابط النداء (افتراضي '/get-destination-address')
 */
const fetchAddress = (
  selectSel,
  addrSel,
  contSel,
  url = '/get-destination-address'
) => {
  const destinationId = $(selectSel).val();
  if (destinationId && destinationId !== '#') {
    $.get(url, { destination_id: destinationId })
      .done(response => {
        const text = response.address || 'لم يتم العثور على العنوان.';
        $(addrSel).text(text);
        $(contSel).show();
      })
      .fail(() => {
        alert('حدث خطأ في جلب العنوان. يرجى المحاولة لاحقًا.');
      });
  } else {
    $(contSel).hide();
  }
};


// ==============================
// التهيئة عند تحميل الوثيقة
// ==============================

$(document).ready(() => {

  // تهيئة DataTable
  const table = new DataTable('.myTable');

  // تهيئة Select2
  $('.js-example-basic-single').select2();

  // حصر الإدخال على الأرقام والنقطة فقط
  $('.number-only').on('input', function() {
    this.value = this.value.replace(/[^0-9.]/g, '');
  });

  // عند تغيير وجهة التحويل الرئيسي
  $('#destination_transfer').on('change', () => {
    console.log('Destination Transfer ID:', $('#destination_transfer').val());
    fetchAddress(
      '#destination_transfer',
      '#destination_address',
      '#destination_address_container'
    );
  });

  // عند تغيير وجهة التحويل بالـ SYP (سعر الصرف + العنوان)
  $('#destination_syp').on('change', () => {
    const id = $('#destination_syp').val();
    console.log('Destination SYP ID:', id);

    // جلب سعر الصرف
    if (id) {
      $.get('/get-exchange-rate', { destination_id: id })
        .done(response => {
          if (response.success) {
            $('#exchange_rate_syp').val(response.exchange_rate);
          } else {
            $('#exchange_rate_syp').val('');
            alert(response.message);
          }
        })
        .fail(() => {
          alert('حدث خطأ أثناء جلب سعر الصرف.');
        });
    }

    // جلب العنوان
    fetchAddress(
      '#destination_syp',
      '#destination_address_syp',
      '#destination_address_container_syp'
    );
  });

  // زر تعديل المبلغ (limited)
  $('.update-btn').on('click', function() {
    const $btn = $(this);
    const $input = $btn.prev('.limited-input');
    const value = $input.val();
    const requestId = $btn.data('id');

    // تحقق أولي
    if (!value || isNaN(value) || value < 0) {
      return Swal.fire({
        title: 'خطأ!',
        text: 'يرجى إدخال مبلغ صحيح.',
        icon: 'error',
        confirmButtonText: 'حسناً'
      });
    }

    // تأكيد المستخدم
    Swal.fire({
      title: 'هل أنت متأكد؟',
      text: 'سيتم تعديل المبلغ!',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'نعم، قم بالتعديل!',
      cancelButtonText: 'إلغاء'
    }).then(result => {
      if (!result.isConfirmed) return;

      const csrfToken = $('meta[name="csrf-token"]').attr('content');
      $.ajax({
        url: `/update-limited/${requestId}`,
        method: 'PUT',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        data: { limited: value }
      })
        .done(response => {
          if (response.success) {
            Swal.fire({
              title: 'تم التعديل بنجاح!',
              icon: 'success',
              timer: 1500,
              showConfirmButton: false
            });
            $input.val(value);
          } else {
            Swal.fire({
              title: 'خطأ!',
              text: 'حدث خطأ، يرجى المحاولة مرة أخرى.',
              icon: 'error',
              confirmButtonText: 'حسناً'
            });
          }
        })
        .fail(() => {
          Swal.fire({
            title: 'خطأ في الاتصال!',
            text: 'حدث خطأ في الاتصال.',
            icon: 'error',
            confirmButtonText: 'حسناً'
          });
        });
    });
  });

  // زر تحديث كلمة المرور
  $('.update-password-btn').on('click', function() {
    const $btn = $(this);
    const $input = $btn.prev('.password-input');
    const pwd = $input.val();
    const requestId = $btn.data('id');

    if (!pwd || pwd.length < 6) {
      return Swal.fire({
        title: 'خطأ!',
        text: 'يجب أن تحتوي كلمة المرور على 6 أحرف على الأقل.',
        icon: 'error',
        confirmButtonText: 'حسناً'
      });
    }

    Swal.fire({
      title: 'هل أنت متأكد؟',
      text: 'سيتم تحديث كلمة المرور!',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'نعم، قم بالتحديث!',
      cancelButtonText: 'إلغاء'
    }).then(result => {
      if (!result.isConfirmed) return;

      const csrfToken = $('meta[name="csrf-token"]').attr('content');
      $.ajax({
        url: `/update-password/${requestId}`,
        method: 'PUT',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        data: { password: pwd }
      })
        .done(response => {
          if (response.success) {
            Swal.fire({
              title: 'تم التحديث بنجاح!',
              icon: 'success',
              timer: 1500,
              showConfirmButton: false
            });
            $input.attr('value', pwd);
          } else {
            Swal.fire({
              title: 'خطأ!',
              text: 'حدث خطأ، يرجى المحاولة مرة أخرى.',
              icon: 'error',
              confirmButtonText: 'حسناً'
            });
          }
        })
        .fail(() => {
          Swal.fire({
            title: 'خطأ في الاتصال!',
            text: 'حدث خطأ في الاتصال.',
            icon: 'error',
            confirmButtonText: 'حسناً'
          });
        });
    });
  });

  // زر التفعيل/الإلغاء (toggle stop)
  $('.toggle-stop-btn').on('click', function() {
    const $btn = $(this);
    const field = $btn.data('field') || $btn.data('field2');
    const requestId = $btn.data('id');
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    Swal.fire({
      title: 'هل أنت متأكد؟',
      text: 'سيتم تحديث الحالة!',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'نعم، حدث التحديث!',
      cancelButtonText: 'إلغاء'
    }).then(result => {
      if (!result.isConfirmed) return;

      $.ajax({
        url: `/toggle-stop-movements/${requestId}`,
        method: 'PUT',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        data: { field }
      })
        .done(response => {
          if (response.success) {
            Swal.fire({
              title: 'تم التحديث بنجاح!',
              icon: 'success',
              timer: 1500,
              showConfirmButton: false,
              width: '300px',
              padding: '10px',
              backdrop: 'rgba(0,0,0,0.4)',
              customClass: { popup: 'animated bounceIn' }
            });

            // تحديث نص الزر وألوانه
            let newText;
            if (['Slice_type_1','Slice_type_2'].includes(field)) {
              newText = response[field] ? 'الشريحة الثانية' : 'الشريحة الأولى';
            } else {
              newText = response[field] ? 'مفعل' : 'غير مفعل';
            }
            $btn.text(newText)
                .toggleClass('bg-green-500 hover:bg-green-600 focus:ring-green-600', response[field])
                .toggleClass('bg-red-500 hover:bg-red-600 focus:ring-red-600', !response[field]);

          } else {
            Swal.fire({
              title: 'خطأ!',
              text: 'حدث خطأ، يرجى المحاولة مرة أخرى.',
              icon: 'error',
              timer: 1500,
              showConfirmButton: false,
              width: '300px',
              padding: '10px',
              backdrop: 'rgba(0,0,0,0.4)',
              customClass: { popup: 'animated shake' }
            });
          }
        })
        .fail(() => {
          Swal.fire({
            title: 'خطأ في الاتصال!',
            text: 'حدث خطأ في الاتصال بالخادم.',
            icon: 'error',
            timer: 1000,
            showConfirmButton: false,
            width: '300px',
            padding: '10px',
            backdrop: 'rgba(0,0,0,0.4)',
            customClass: { popup: 'animated shake' }
          });
        });
    });
  });

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
