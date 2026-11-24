<div class="jb-wrap">
  <div class="row g-4">
    <div class="col-md-4">
      <div class="border rounded p-3 bg-white">
        <div class="mb-3">
          <label for="jbi-sn" class="form-label d-block fw-semibold"><{$smarty.const._MA_JILLBOOKIN_HELPER_ITEM}></label>
          <select name="jbi_sn" id="jbi-sn" size="8" class="form-control form-select w-100 jb-listbox">
            <{foreach from=$items item=item}>
              <option value="<{$item.jbi_sn}>"><{$item.jbi_title}></option>
            <{/foreach}>
          </select>
        </div>

        <div class="mb-3">
          <label for="booking-date" class="form-label fw-semibold"><{$smarty.const._MA_JILLBOOKIN_HELPER_DATE}></label>
          <div class="input-group">
            <input id="booking-date" type="text" placeholder="<{$smarty.const._MA_JILLBOOKIN_HELPER_DATE_HINT}>" class="form-control">
            <button type="button" id="date-img" class="btn btn-outline-secondary" onclick="WdatePicker({el:'booking-date', minDate:'%y-%M-%d'})" aria-label="<{$smarty.const._MA_JILLBOOKIN_HELPER_DATE_HINT}>">
              <i class="fa-regular fa-calendar" aria-hidden="true"></i>
            </button>
          </div>
        </div>

        <div class="mb-0">
          <label for="booking-event" class="form-label fw-semibold"><{$smarty.const._MA_JILLBOOKIN_HELPER_EVENT}></label>
          <input id="booking-event" type="text" placeholder="<{$smarty.const._MD_INDIVIDUAL_BOOKING}>" class="form-control">
        </div>
      </div>
    </div>

    <div class="col-md-8">
      <div class="border rounded p-3 bg-white">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
          <button type="button" class="btn btn-primary" id="get-time-btn">
            <{$smarty.const._MA_JILLBOOKIN_HELPER_GET}>
          </button>
          <button type="button" class="btn btn-primary" id="order-btn" style="display: none;">
            <{$smarty.const._MA_JILLBOOKIN_HELPER_ORDER}>
          </button>
        </div>

        <div id="message" role="status" aria-live="polite" style="display: none;"></div>
        <div id="jbi-date-text" class="fw-semibold mb-3"></div>
        <div id="time-list"></div>
      </div>
    </div>
  </div>
</div>

<script>
  ;(function($) {
    let jbiSnSelect = $('#jbi-sn'),
      bookingDateInput = $('#booking-date'),
      jbiDateText = $('#jbi-date-text'),
      timeList = $('#time-list'),
      dateImg = $('#date-img'),
      eventInput = $('#booking-event'),
      getTimeBtn = $('#get-time-btn'),
      orderBtn = $('#order-btn'),
      msgBlock = $('#message');

    const initData = {
      'jb_date': null,
      'jbi_sn' : null
    };

    const weekdays = ['<{$smarty.const._MD_JILLBOOKIN_W0}>', '<{$smarty.const._MD_JILLBOOKIN_W1}>', '<{$smarty.const._MD_JILLBOOKIN_W2}>', '<{$smarty.const._MD_JILLBOOKIN_W3}>', '<{$smarty.const._MD_JILLBOOKIN_W4}>', '<{$smarty.const._MD_JILLBOOKIN_W5}>', '<{$smarty.const._MD_JILLBOOKIN_W6}>'];

    let data = {...initData};

    bookingDateInput.on('blur', init);
    dateImg.on('click', init);
    jbiSnSelect.on('change', init);

    getTimeBtn.on('click', getTimesOfItem);

    orderBtn.on('click', () => {
      let values = [];
      $('#time-list input[type=checkbox]:checked').each((_, item) => values.push(+item.value));

      if (values.length === 0) {
        return;
      }

      $.ajax({
        type: 'POST',
        url: `${location.href}?op=create_orders&XOOPS_TOKEN_REQUEST=<{$token|default:''}>`,
        async: true,
        data: JSON.stringify({...data, event: eventInput.val(), values}),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        error: xhr => msgBlock.attr('class', 'alert alert-danger').text(xhr.responseText).show(),
        success: data => {
          if (!data.result) {
            msgBlock.attr('class', 'alert alert-danger').text(data.msg).show();
            return;
          }
          init();
          getTimeBtn.hide();
          msgBlock.attr('class', 'alert alert-success').text("<{$smarty.const._MA_JILLBOOKIN_HELPER_DONE}>").show();
          setTimeout(() => {
            msgBlock.text(null).hide();
            getTimeBtn.show();
          }, 2000);
        }
      });
    });

    init();

    // 初始化
    function init() {
      jbiDateText.text(null);
      timeList.text(null);
      getTimeBtn.show();
      orderBtn.hide();
      data = {...initData};
    }

    // 取得某場地的全部時段
    function getTimesOfItem() {
      if (bookingDateInput.val() === '' || jbiSnSelect.val() === '') {
        return;
      }

      data.jb_date = bookingDateInput.val();

      let weekday = weekdays[(new Date(data.jb_date)).getDay()];
      jbiDateText.text(`<{$smarty.const._MA_JILLBOOKIN_HELPER_BOOK_DATE}> ${data.jb_date} (${weekday})`);

      data.jbi_sn = +jbiSnSelect.val();

      timeList.text('<{$smarty.const._MA_JILLBOOKIN_HELPER_LOADING}>');
      let dayOfWeek = new Date(data.jb_date).getDay();
      $.get(`${location.href}?op=get_times_of_item&sn=${data.jbi_sn}&date=${data.jb_date}`)
        .then(data => {
          let items = getTimeByDay(data, dayOfWeek);
          getTimeBtn.hide();
          (items && items.length > 0) ? generateTimeList(items) : timeList.text('<{$smarty.const._MA_JILLBOOKIN_HELPER_NONE}>');
        }).catch(err => console.log(err));
    }

    // 取得當天開放之時段
    function getTimeByDay(data, day) {
      if (!data) {
        return null;
      }
      return data.filter(item => {
        return (item.jbt_week + '').indexOf(day) > -1;
      });
    }

    // 產生時段列表
    function generateTimeList(items) {
      timeList.html('<div class="list-group"></div>');
      let listGroup = timeList.find('.list-group');
      items.forEach(item => {
        let stateClass = item.can_order ? '' : 'list-group-item-light jb-cant-use';
        let iconHtml = item.can_order
          ? '<i class="fa-solid fa-check jb-text-success" aria-hidden="true"></i>'
          : '<i class="fa-solid fa-minus jb-text-muted" aria-hidden="true"></i>';
        let detailText = item.can_order ? '' : ` <span class="small">by ${item.order_user} (${item.event})</span>`;
        listGroup.append(`
          <label class="list-group-item d-flex align-items-center gap-3 mb-0 ${stateClass}" style="min-height: 44px; cursor: ${item.can_order ? 'pointer' : 'not-allowed'};">
            <input type="checkbox" class="form-check-input m-0 flex-shrink-0" value="${item.jbt_sn}" ${item.can_order ? '' : 'disabled'}>
            ${iconHtml}
            <span class="fw-medium">${item.jbt_title}</span>
            ${detailText}
          </label>
        `);
      });
      orderBtn.show();
    }
  })($);
</script>
