<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Company;
use App\Invoice;
use App\Employee;
use App\Exports\CompanyExport;
use App\Exports\UsersExport;

class ReportController extends Controller
{
    public function payroll(Request $request)
    {
        $invoiceId = $request->input('id');
        $invoice = Invoice::find($invoiceId);
        $invoicesArray = [];
        $invoicesArray[] = ['First Name','Last Name','Range','Bank Account','Bank Name','Bank Account Owner','Account Type','Amount'];
        $payrolls = [];
        $itemArray = [];
        foreach($invoice->items as $item){
            if($item->employee_id){
                if(isset($payrolls[$item->employee_id])){
                    if($item->pay=="company")$payrolls[$item->employee_id]+=$item->total;
                    else $payrolls[$item->employee_id]-=$item->total;
                }
                else {
                    if($item->pay=="company")$payrolls[$item->employee_id]=$item->total;
                    else $payrolls[$item->employee_id]=$item->total*(-1);
                }
            }
        }
        foreach($payrolls as $employeeId =>$total){
            $employee = Employee::find($employeeId);
            $invoicesArray[] = [$employee->first_name,$employee->last_name,$invoice->start_date."~".$invoice->end_date,$employee->bank_account_number,$employee->bank_name,
            $employee->bank_account_name,$employee->bank_account_type,$total];
        }
        $export = new CompanyExport([
            $invoicesArray
        ]);
        $fileName = $invoice->company->name.'Payroll-'.$invoice->invoicing_date;
        return Excel::download($export,$fileName.'.xlsx');   
    }
    public function revenueMonth(Request $request)
    {
        $year = $request->input('year');
        $month = $request->input('month');
        if(strlen($month)==1) $month = '0'.$month;
        $invoices = Invoice::where('invoicing_date','like',$year.'-'.$month.'%')->get();
        $companyFees = []; 
        foreach($invoices as $invoice){
            foreach($invoice->items as $item){
                if($item->slug == 'company-fee' || $item->slug == 'fee-'.$item->employee_id ){
                    if(isset($companyFees[$invoice->company_id])){
                        $companyFees[$invoice->company_id] += $item->total;
                    }else{
                        $companyFees[$invoice->company_id] = $item->total;
                    }
                }
            }
        }
        $revenueArray = [];
        $revenueArray[] = ['Company','Revenue'];
        $total = 0;
        foreach( $companyFees as $comanyId=>$companyFee){
            $company = Company::find($comanyId);
            $revenueArray[] = [$company->name,$companyFee];
            $total +=$companyFee;
        }
        $revenueArray[] = ['Total',$total];
        $export = new CompanyExport([
            $revenueArray
        ]);
        $fileName = 'Revenue-'.$year.'-'.$month;
        return Excel::download($export,$fileName.'.xlsx');   
    }
    public function companyMembers(Request $request)
    {
        $companies = Company::where('status',1)->get();
        $membersArray = [];
        $membersArray[] = ['Company','Members'];
        $total = 0;
        foreach($companies as $company){
            $membersArray[] = [$company->name,count($company->activeContracts)];
            $total += count($company->activeContracts);
        }
        $membersArray[] = ['Total',$total];
        $export = new CompanyExport([
            $membersArray
        ]);
        return Excel::download($export,'companyMembers.xlsx');   
    }
    public function revenue(Request $request)
    {
        $year = $request->input('year');
        $invoices = Invoice::where('invoicing_date','like',$year.'-%')->get();
        $amounts = ['hours-sales'=>[],'company-fee'=>[],'member-fee'=>[],'deduction'=>[]]; 
        $monthes = [];
        foreach($invoices as $invoice){
            $month = date('m',strtotime($invoice->invoicing_date));
            $m = date('M',strtotime($invoice->invoicing_date));
            $monthes[$month] = $m;
            foreach($invoice->items as $item){
                if($item->slug == 'company-fee'){
                    $slug = "company-fee";
                }else if($item->slug == 'fee-'.$item->employee_id){
                    $slug = "member-fee";
                }else if($item->slug == 'deduction-'.$item->employee_id){
                    $slug = "deduction";
                }else{
                    $slug = "hours-sales";
                }
                if(isset($amounts[$slug][$month])){
                    $amounts[$slug][$month] += $item->total;
                }else{
                    $amounts[$slug][$month] = $item->total;
                }
            }
        }
        $monthItems = [''];
        foreach($monthes as $month=>$m){
            $monthItems[] = $m;
        }
        $monthItems[] = 'Total';
        $revenueArray = [];
        $revenueArray[] = $monthItems;
        $total = 0;
        $invoiced = ['Amount Invoiced'];
        foreach($monthes as $month=>$m){
            $invoiced[] = $amounts['hours-sales'][$month] + $amounts['company-fee'][$month];
            $total = $total + $amounts['hours-sales'][$month] + $amounts['company-fee'][$month];
        }
        $invoiced[] = $total;
        $revenueArray[] = $invoiced;
        $total = 0;
        $payroll = ['Paid To Employees'];
        foreach($monthes as $month=>$m){
            $pay = $amounts['hours-sales'][$month] - $amounts['member-fee'][$month];
            if(isset($amounts['deduction'][$month])) $pay -= $amounts['deduction'][$month];
            $payroll[] = $pay;
            $total = $total + $pay;
        }
        $payroll[] = $total;
        $revenueArray[] = $payroll;
        $total = 0;
        $memberFee = ['Member Fee'];
        foreach($monthes as $month=>$m){
            $pay = $amounts['member-fee'][$month];
            $memberFee[] = $pay;
            $total = $total + $pay;
        }
        $memberFee[] = $total;
        $revenueArray[] = $memberFee;
        $total = 0;
        $companyFee = ['Company Fee'];
        foreach($monthes as $month=>$m){
            $pay = $amounts['company-fee'][$month];
            $companyFee[] = $pay;
            $total = $total + $pay;
        }
        $companyFee[] = $total;
        $revenueArray[] = $companyFee;
        $total = 0;
        $deduction = ['Deduction Fee'];
        foreach($monthes as $month=>$m){
            if(isset($amounts['deduction'][$month])) {
                $pay = $amounts['deduction'][$month];
                $total = $total + $pay;
            }
            else $pay = '';
            $deduction[] = $pay;
        }
        $deduction[] = $total;
        $revenueArray[] = $deduction;
        $export = new CompanyExport([
            $revenueArray
        ]);
        $fileName = 'Revenue-'.$year;
        return Excel::download($export,$fileName.'.xlsx');   
    }
}