-- ///////////////////////////////////////////////////////////
-- // PROJECTOR                                             //
-- //-------------------------------------------------------//
-- // Version : 4.1.0                                       //
-- // Date : 2013-11-14                                     //
-- ///////////////////////////////////////////////////////////
--
--

DELETE FROM `${prefix}columnselector` WHERE attribute='idTicketType' and hidden='1';

UPDATE `${prefix}columnselector` set attribute='idTicketType', field='nameTicketType'
WHERE attribute='idticketType';

DELETE FROM `${prefix}columnselector` WHERE attribute='requestRefreshProject';

DELETE FROM `${prefix}workelement` where (refType, refId) in 
(select refType, refId from (select * from `${prefix}workelement` w) ww group by refType, refId having count(*) > 1)
and plannedWork is null and realWork is null;