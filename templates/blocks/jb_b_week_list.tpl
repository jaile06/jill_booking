<style type="text/css" media="screen">
  .resp-vtabs .resp-tabs-container{
    min-height: <{$block.height}>px;
  }
</style>
<div class="jb-wrap">
  <div id="iteamtab<{$block.randStr}>" class="clearfix">
    <ul class="resp-tabs-list vert">
      <{foreach from=$block.content item=data}>
        <li><{$data.jbi_title}></li>
      <{/foreach}>
    </ul>
    <div class="resp-tabs-container vert">
      <{foreach from=$block.content item=data}>
        <div class="table-responsive">
          <table class="table table-sm table-hover table-bordered align-middle mb-0">
            <caption class="visually-hidden"><{$data.jbi_title}></caption>
            <thead class="jb-table-head">
              <tr>
                <th scope="col" class="text-center">
                  <div class="small fw-normal"><{$block.weekArr.0.y}></div>
                  <div><{$smarty.const._MB_JSESSION}></div>
                </th>
                <{foreach from=$block.weekArr item=week}>
                  <th scope="col" class="text-center">
                    <div><{$week.md}></div>
                    <div class="small fw-normal"><{$week.w}></div>
                  </th>
                <{/foreach}>
              </tr>
            </thead>
            <tbody>
              <{if $data.timeArr|default:false}>
                <{foreach from=$data.timeArr key=t item=time}>
                  <tr>
                    <th scope="row" class="text-center align-middle fw-medium"><{$time.jbt_title}></th>
                    <{foreach from=$block.weekArr key=wk item=week}>
                      <td class="text-center align-middle">
                        <div style="color:<{$data.bookingArr.$t.$wk.color}>;">
                          <{$data.bookingArr.$t.$wk.content}>
                        </div>
                      </td>
                    <{/foreach}>
                  </tr>
                <{/foreach}>
              <{/if}>
            </tbody>
          </table>
        </div>
      <{/foreach}>
    </div>
  </div>

  <div class="d-flex justify-content-end mt-2">
    <a href="<{$xoops_url}>/modules/jill_booking/index.php" class="jb-action jb-action-primary">
      <{$smarty.const._MB_JILLBOOKIN_MORE}> <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
    </a>
  </div>
</div>
