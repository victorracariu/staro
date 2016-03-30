DELETE FROM Item;
DELETE FROM ItemCategory;
DELETE FROM ItemSubategory;
DELETE FROM ItemBrand;

DELETE FROM Partner;

DELETE FROM BuyInvoice;
DELETE FROM BuyInvoiceLine;
DELETE FROM BuyDelivery;
DELETE FROM BuyDeliveryLine;
DELETE FROM BuyOrder;
DELETE FROM BuyOrderLine;
DELETE FROM BuyOrderStatus;

DELETE FROM SaleInvoice;
DELETE FROM SaleInvoiceLine;
DELETE FROM SaleDelivery;
DELETE FROM SaleDeliveryLine;
DELETE FROM SaleOrder;
DELETE FROM SaleOrderLine;
DELETE FROM SaleOrderStatus;

DELETE FROM StockOperation;
DELETE FROM StockStatus;
DELETE FROM StockTransfer;
DELETE FROM StockTransferLine;
DELETE FROM StockReceipt;
DELETE FROM StockReceiptLine;

DELETE FROM LedgerNote;
DELETE FROM LedgerLine;
DELETE FROM MoneyOperation;
DELETE FROM MoneyInvoice;

===============================================================================

DROP TABLE ProdDoc;
DROP TABLE ProdDocLine;
DROP TABLE ProdOption;
DROP TABLE ProdOperation;

DROP TABLE ProdBom;
DROP TABLE ProdBomLine;
DROP TABLE ProdBomOperation;
DROP TABLE ProdBomOption;

DROP TABLE ProdOrder;
DROP TABLE ProdOrderLine;
DROP TABLE ProdOrderOperation;
DROP TABLE ProdOrderOption;

DROP TABLE ProdConsum;
DROP TABLE ProdConsumLine;
DROP TABLE ProdReceipt;
DROP TABLE ProdReceiptLine;

DROP TABLE ProdResource;
DROP TABLE ProdWorkplace;
