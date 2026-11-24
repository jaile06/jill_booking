# jill_booking 開發規範

> 通用規則由全域 Skill `xoops-core-dev` 與範本 `OpenSpec-XOOPS-convention.md` 提供，此處不重複。
> 本檔只寫本模組的事實與例外；與通用規則衝突時以本檔為準。

## 1. 專案資訊

| 欄位 | 值 |
|---|---|
| 命名空間 | `XoopsModules\Jill_booking\` |
| XOOPS | 2.5.11 與 2.7.4，同一份程式碼 |
| PHP | 8.3 以上 |
| 2.5.11 測試站 | `C:/my_xoops2511`（2.5.11-Stable，PHP 8.3.0，Smarty 3.1.48） |
| 2.7.4 開發站 | `E:/my_xoops274` |
| 相依 | TadTools 4.0.1 以上（2.5.11 站實裝 4.0.1，2.7.4 站 4.0.8；只用兩版共有的 API） |
| 佈景 | 2.5.11 只支援 Bootstrap 5 |
| 儲存引擎 | MyISAM，無交易 |

不可變更：資料表與主鍵、入口網址、區塊函式名稱、偏好設定名稱。

| 資料表 | 用途 | 主鍵 |
|---|---|---|
| `jill_booking` | 預約主檔 | `jb_sn` |
| `jill_booking_week` | 批次預約的星期與時段 | `jb_sn, jb_week, jbt_sn` |
| `jill_booking_date` | 預約明細、候補、審核 | `jb_sn, jb_date, jbt_sn` |
| `jill_booking_item` | 場地；`jbi_approval` 為逗號分隔 uid | `jbi_sn` |
| `jill_booking_time` | 時段 | `jbt_sn` |

## 2. 相容例外

- 資料存取用 `Utility::query($sql, $types, $params)`，取代通用規範的 `$xoopsDB->query() or Utility::web_error()`。它失敗時丟例外。
- `IN (...)` 每個值各一個 `?`，不可綁定逗號字串。
- 禁用 `$xoopsDB->exec()`：2.5.11 沒有。
- `Utility::query()` 直接用底層連線，核心的 GET 寫入保護擋不到，所以寫入 op 一律要驗 token。
- 樣板只用 Smarty 3.1 與 4.5 共通語法。

## 3. 權限

- 權限每次請求重算，不可用 `??=` 快取在 session。
- 審核權以場地為單位：操作某筆預約，須是該場地審核者、模組管理員或預約本人。
- 修改、取消預約一律查資料庫的擁有者，不信任請求帶來的 `jb_sn`。

## 4. 資料規則

- 主檔在明細全部刪完後才刪。
- 寫入預約一律經 `Jill_booking::store()`，失敗時回滾；批次第一步只存 session。
- 場地或時段仍有預約時拒絕刪除，提示改為停用。
- 沒有日期存 NULL（`jbi_end` 為不限、`pass_date` 為未審核），不寫入也不比對 `'0000-00-00'`。
- 升級程式須可重複執行；孤兒資料只列報告，不自動刪除。

## 5. 檢查

`php D:/workspace/02_Tools/xoops-lint/xoops-lint.php <模組目錄>`

基準（2026-11-24，改版前）：103 error（ENTRY-SQL 92、VAR-VAR 11）。完工目標 0 error。
