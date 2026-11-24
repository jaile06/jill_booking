# 3.0 舊版流程基準

環境：2.5.11-Stable、PHP 8.3.0、Smarty 3.1.48、school2022（Bootstrap 5）。日期 2026-11-24。

## 訪客（空資料）

| 請求 | 舊版結果 | 預期 |
|---|---|---|
| GET index / batch / list | 200 | 維持 |
| GET listapproval | 302 回首頁 | 維持 |
| GET admin/main、admin/save_sort | 302 到登入頁 | 維持 |
| GET batch `op=list_jill_booking`，不存在的 jb_sn | XOOPS 錯誤頁（未讀到例外內容） | 正常提示 |
| POST index `single_insert_booking` | XOOPS 錯誤頁（未讀到例外內容） | 拒絕 |
| POST index `save_jb_booking_content` | 200，未擋，`<b>x</b>` 原樣回傳 | 拒絕並跳脫 |
| POST batch `insert_jill_booking`，無 token | 302 擋下 | 維持 |
| POST batch `insert_jill_booking_date`，不存在的 jb_sn | **寫入一筆明細** | 拒絕 |
| GET index `delete_booking`，不存在的時段 | 302，無資料可刪 | 改為需 token |

## 登入角色

待補：管理員、一般使用者、審核者。
