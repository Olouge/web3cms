<?php

// i18n - Russian Vice-Versa Language Pack (Links)
$retval=array(
    'Add a company' => '�������� ��������',
    'Add a company payment' => '�������� ����� ��������',
    'Add an expense' => '�������� ������',
    'Add an invoice' => '�������� ����',
    'Add a location' => '�������� ���������������',
    'Add a member' => '�������� ���������',
    'Add a project' => '�������� ������',
    'Add a task' => '�������� ������',
    'Add a time record' => '�������� ������ �������',
    'Apply selected user interface' => '��������� ��������� ������� ���',
    'Authenticate using my member account' => '������������� ��������� ��� ������� ������',
    'Browse companies' => '����������� ��������',
    'Browse company payments' => '����������� ������� ��������',
    'Browse expenses' => '����������� �������',
    'Browse invoices' => '����������� �����',
    'Browse members' => '����������� ����������',
    'Browse projects' => '����������� �������',
    'Browse tasks' => '����������� ������',
    'Browse time' => '����������� �����',
    'Cancel[form]' => '��������',
    'Change interface' => '������� ������� ���',
    'Companies' => '��������',
    'Company' => '��������',
    'Company payment' => '����� ��������',
    'Company payment number "{paymentNumber}"' => '����� �������� ����� "{paymentNumber}"',
    'Company payments' => '������ ��������',
    'Confirm' => '�����������',
    'Contact' => '�������',
    'Contact us' => '��������� � ����',
    'Create' => '�������',
    'Create a new company' => '������� ����� ��������',
    'Create a new company payment' => '������� ����� ����� ��������',
    'Create a new expense' => '������� ����� ������',
    'Create a new invoice' => '������� ����� ����',
    'Create a new location' => '������� ����� ���������������',
    'Create a new member' => '������� ������ ���������',
    'Create a new member account' => '������� ����� ������� ������',
    'Create a new project' => '������� ����� ������',
    'Create a new task' => '������� ����� ������',
    'Create a new time record' => '������� ����� ������ �������',
    'Delete' => '�������',
    'Delete expense' => '������� ������',
    'Delete the record number {id}' => '������� ������ ����� {id}',
    'Delete time record' => '������� ������ �������',
    'Edit' => '�������������',
    'Edit company' => '������������� ��������',
    'Edit company payment' => '������������� ����� ��������',
    'Edit expense' => '������������� ������',
    'Edit invoice' => '������������� ����',
    'Edit location' => '������������� ���������������',
    'Edit member\'s profile' => '������������� ������� ���������',
    'Edit my profile' => '������������� ��� �������',
    'Edit project' => '������������� ������',
    'Edit task' => '������������� ������',
    'Edit time record' => '������������� ������ �������',
    'Expense' => '������',
    'Expense number "{expenseNumber}"' => '������ ����� "{expenseNumber}"',
    'Expenses' => '�������',
    'Home' => '��������',
    'Invoice' => '����',
    'Invoice {number}' => '���� {number}',
    'Invoice number "{invoiceNumber}"' => '���� ����� "{invoiceNumber}"',
    'Invoices' => '�����',
    'Grid of companies' => '������� ��������',
    'Grid of company payments' => '������� �������� ��������',
    'Grid of expenses' => '������� ��������',
    'Grid of invoices' => '������� ������',
    'Grid of locations' => '������� ���������������',
    'Grid of members' => '������� ����������',
    'Grid of projects' => '������� ��������',
    'Grid of tasks' => '������� �����',
    'Grid of time records' => '������� ������� �������',
    'Leave my member account' => '�������� ��� ������� ������',
    'List of companies' => '������ ��������',
    'List of company payments' => '������ �������� ��������',
    'List of expenses' => '������ ��������',
    'List of invoices' => '������ ������',
    'List of locations' => '������ ���������������',
    'List of members' => '������ ����������',
    'List of projects' => '������ ��������',
    'List of tasks' => '������ �����',
    'List of time records' => '������ ������� �������',
    'Locations' => '���������������',
    'Log in' => '������������� �������',
    'Login' => '�����������',
    'Logout' => '�����',
    'Main page' => '��������� ��������',
    'Member' => '��������',
    'Members' => '���������',
    'My company' => '��� ��������',
    'My profile' => '��� �������',
    'Payment {number} ({method})' => '����� {number} ({method})',
    'Project' => '������',
    'Projects' => '�������',
    'Register' => '�����������',
    'Register member account' => '���������������� ������� ������',
    'Save' => '���������',
    '"{screenName}" member' => '�������� "{screenName}"',
    'Show' => '��������',
    'Show company' => '�������� ��������',
    'Show company payment' => '�������� ����� ��������',
    'Show expense' => '�������� ������',
    'Show invoice' => '�������� ����',
    'Show location' => '�������� ���������������',
    'Show member' => '�������� ���������',
    'Show my profile' => '�������� ��� �������',
    'Show project' => '�������� ������',
    'Show task' => '�������� ������',
    'Show time record' => '�������� ������ �������',
    'Tasks' => '������',
    'Time' => '�����',
    'Time records' => '������ �������',
    'Time report' => '����� � �������',
    '"{title}" company' => '�������� "{title}"',
    '"{title}" location' => '��������������� "{title}"',
    '"{title}" project' => '������ "{title}"',
    '"{title}" task' => '������ "{title}"',
    '"{title}" time record' => '������ ������� "{title}"',
    'View as grid' => '�������� � ���� �������',
    'View as list' => '�������� � ���� ������',
    'View my company' => '����������� ��� ��������',
    'View my profile' => '����������� ��� �������',
);
$myfile=dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'_local'.DIRECTORY_SEPARATOR.basename(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.basename(dirname(__FILE__)).DIRECTORY_SEPARATOR.basename(__FILE__);
return (file_exists($myfile) && is_array($myarray=require($myfile))) ? CMap::mergeArray($retval,$myarray) : $retval;