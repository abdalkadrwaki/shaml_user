// ==============================
// imports
// ==============================
import $ from 'jquery';
window.$ = window.jQuery = $;

import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

import 'select2/dist/css/select2.min.css';
import 'select2/dist/js/select2.full.min.js';

import DataTable from 'datatables.net-bs5';

import Swal from 'sweetalert2';
window.Swal = Swal;

import './bootstrap'; // إعدادات Laravel الإضافية


// ==============================
// document ready
// ==============================
$(document).ready(() => {
  // تهيئة DataTable
  new DataTable('.myTable');

  // تهيئة Select2
  $('.js-example-basic-single').select2();

  // حصر الإدخال على الأرقام والنقطة فقط
  $('.number-only').on('input', function() {
    this.value = this.value.replace(/[^0-9.]/g, '');
  });

  // دالة مساعدة لجلب وعرض العنوان
  const fetchAddress = (selectSel, addrSel, contSel, url = '/get-destination-address') => {
    const id = $(selectSel).val();
    if (id && id !== '#') {
      $.get(url, { destination_id: id })
        .done(res => {
          $(addrSel).text(res.address || 'لم يتم العثور على العنوان.');
          $(contSel).show();
        })
        .fail(() => {
          alert('حدث خطأ في جلب العنوان. يرجى المحاولة لاحقًا.');
        });
    } else {
      $(contSel).hide();
    }
  };

  // عند تغيير وجهة التحويل الرئيسي
  $('#destination_transfer').on('change', function() {
    console.log('Destination Transfer ID:', $(this).val());
    fetchAddress(
      '#destination_transfer',
      '#destination_address',
      '#destination_address_container'
    );
  });

  // عند تغيير وجهة SYP (سعر الصرف + عنوان)
  $('#destination_syp').on('change', function() {
    const id = $(this).val();
    console.log('Destination SYP ID:', id);

    // جلب سعر الصرف
    if (id) {
      $.get('/get-exchange-rate', { destination_id: id })
        .done(res => {
          if (res.success) {
            $('#exchange_rate_syp').val(res.exchange_rate);
          } else {
            $('#exchange_rate_syp').val('');
            alert(res.message);
          }
        })
        .fail(() => alert('حدث خطأ أثناء جلب سعر الصرف.'));
    }

    // جلب العنوان
    fetchAddress(
      '#destination_syp',
      '#destination_address_syp',
      '#destination_address_container_syp'
    );
  });

  // تعديل المبلغ المحدود
  $('.update-btn').on('click', function() {
    const $btn = $(this);
    const $input = $btn.prev('.limited-input');
    const value = $input.val();
    const requestId = $btn.data('id');

    if (!value || isNaN(value) || value < 0) {
      return Swal.fire({
        title: 'خطأ!',
        text: 'يرجى إدخال مبلغ صحيح.',
        icon: 'error',
        confirmButtonText: 'حسناً'
      });
    }

    Swal.fire({
      title: 'هل أنت متأكد؟',
      text: 'سيتم تعديل المبلغ!',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'نعم، قم بالتعديل!',
      cancelButtonText: 'إلغاء'
    }).then(result => {
      if (result.isConfirmed) {
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        $.ajax({
          url: `/update-limited/${requestId}`,
          method: 'PUT',
          headers: { 'X-CSRF-TOKEN': csrfToken },
          data: { limited: value }
        })
          .done(res => {
            if (res.success) {
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
      }
    });
  });

  // تحديث كلمة المرور
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
      confirmButtonText: 'نعم، قم بالتحديث!',
      cancelButtonText: 'إلغاء'
    }).then(result => {
      if (result.isConfirmed) {
        const csrf = $('meta[name="csrf-token"]').attr('content');
        $.ajax({
          url: `/update-password/${requestId}`,
          method: 'PUT',
          headers: { 'X-CSRF-TOKEN': csrf },
          data: { password: pwd }
        })
          .done(res => {
            if (res.success) {
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
      }
    });
  });

  // تفعيل/إلغاء التوقف
  $('.toggle-stop-btn').on('click', function() {
    const $btn = $(this);
    const field = $btn.data('field') || $btn.data('field2');
    const requestId = $btn.data('id');

    Swal.fire({
      title: 'هل أنت متأكد؟',
      text: 'سيتم تحديث الحالة!',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'نعم، حدث التحديث!',
      cancelButtonText: 'إلغاء'
    }).then(result => {
      if (result.isConfirmed) {
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        $.ajax({
          url: `/toggle-stop-movements/${requestId}`,
          method: 'PUT',
          headers: { 'X-CSRF-TOKEN': csrfToken },
          data: { field }
        })
          .done(res => {
            if (res.success) {
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
              let newText;
              if (['Slice_type_1','Slice_type_2'].includes(field)) {
                newText = res[field] ? 'الشريحة الثانية' : 'الشريحة الأولى';
              } else {
                newText = res[field] ? 'مفعل' : 'غير مفعل';
              }
              $btn.text(newText)
                .toggleClass('bg-green-500 hover:bg-green-600 focus:ring-green-600', res[field])
                .toggleClass('bg-red-500 hover:bg-red-600 focus:ring-red-600', !res[field]);
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
      }
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
