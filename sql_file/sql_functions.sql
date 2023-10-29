DELIMITER $$
CREATE DEFINER=`root`@`localhost` FUNCTION `ConvertGermanDateToDate`(inputText TEXT) RETURNS date
    DETERMINISTIC
BEGIN
    DECLARE monthName TEXT;
    DECLARE year TEXT;
    SET monthName = SUBSTRING_INDEX(inputText, ' ', 1);
    SET year = SUBSTRING_INDEX(inputText, ' ', -1);
    RETURN STR_TO_DATE(CONCAT(year, '-', CASE monthName
        WHEN 'Januar' THEN '01'
        WHEN 'Februar' THEN '02'
        WHEN 'März' THEN '03'
        WHEN 'April' THEN '04'
        WHEN 'Mai' THEN '05'
        WHEN 'Juni' THEN '06'
        WHEN 'Juli' THEN '07'
        WHEN 'August' THEN '08'
        WHEN 'September' THEN '09'
        WHEN 'Oktober' THEN '10'
        WHEN 'November' THEN '11'
        WHEN 'Dezember' THEN '12'
        END, '-01'), '%Y-%m-%d');
END$$
DELIMITER ;