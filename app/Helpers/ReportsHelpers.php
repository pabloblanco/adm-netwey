<?php
namespace App\Helpers;

use Carbon\Carbon;

class ReportsHelpers
{
  public static function getArraySalesReport($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {
        $seller_name = !empty($r->user_name) ? ($r->user_name . ' ' . $r->user_last_name) : 'N/A';
        $installer_name = !empty($r->installer_name) ? ($r->installer_name . ' ' . $r->installer_last_name) : 'N/A';
        $client = $r->client_name . ' ' . $r->client_lname;

        switch ($r->sale_type) {
          case 'T':$type_sale = 'Telefonía';
            break;
          case 'M':$type_sale = 'MIFI Nacional';
            break;
          case 'MH':$type_sale = 'MIFI Altan';
            break;
          case 'F':$type_sale = 'Fibra';
            break;
          default:$type_sale = 'Internet Hogar';
            break;
        }

        $type = $r->type == 'P' ? 'Alta' : 'Recarga';
        $pack = $r->type == 'P' ? $r->pack : 'N/A';
        $artic = $r->type == 'P' ? $r->article : 'N/A';
        $conc = !empty($r->concentrator) ? $r->concentrator : 'N/A';
        $phone = !empty($r->client_phone) ? $r->client_phone : 'N/A';
        $origin = $r->type == 'P' ? (!empty($r->from) && $r->from == 'A' ? 'API' : 'Seller') : 'N/A';
        $zone_name = !empty($r->zone_name) ? $r->zone_name : 'N/A';

        $redsocial = 'N/A';

        if (!empty($r->campaign) && $r->type == 'P') {
          $redsocial = $r->campaign;
        }

        $isPhoneRef = 'NO';
        $phoneRefBy = 'N/A';
        if ($r->sale_type == 'T') {
          if (!empty($r->isPhoneRef)) {
            if ($r->isPhoneRef == 'Y') {
              $isPhoneRef = 'SI';
              $phoneRefBy = !empty($r->phoneRefBy) ? $r->phoneRefBy : 'N/A';
            }
          }
        }

        $reportxls[] = [
          $r->unique_transaction,
          $r->date_reg,
          $conc,
          $seller_name,
          $installer_name,
          $type,
          $pack,
          $artic,
          $r->service,
          $zone_name,
          $r->order_altan,
          $r->codeAltan,
          number_format($r->amount, 2, '.', ','),
          $client,
          $r->msisdn,
          $type_sale,
          $phone,
          $redsocial,
          $origin,
          $isPhoneRef,
          $phoneRefBy];
      }
    }
  }

  public static function getArrayRechargeReport($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {
        $seller_name = !empty($r->user_name) ? ($r->user_name . ' ' . $r->user_last_name) : 'N/A';
        $installer_name = !empty($r->installer_name) ? ($r->installer_name . ' ' . $r->installer_last_name) : 'N/A';
        $folio = !empty($r->folio) && (!empty($r->concentrator) && $r->concentrator == 'OXXO') ? $r->folio : 'N/A';
        $conc = !empty($r->concentrator) ? $r->concentrator : 'N/A';

        switch ($r->sale_type) {
          case 'T':$type_sale = 'Telefonía';
            break;
          case 'M':$type_sale = 'MIFI Nacional';
            break;
          case 'MH':$type_sale = 'MIFI Altan';
            break;
          case 'F':$type_sale = 'Fibra';
            break;
          default:$type_sale = 'Internet Hogar';
            break;
        }

        $phone = !empty($r->client_phone) ? $r->client_phone : 'N/A';
        $phone2 = !empty($r->client_phone2) ? $r->client_phone2 : 'N/A';
        $lat = !empty($r->lat) ? $r->lat : 'N/A';
        $lng = !empty($r->lng) ? $r->lng : 'N/A';
        $amount = number_format($r->amount, 2, '.', ',');
        $conciliation = $r->conciliation == 'Y' ? 'SI' : 'NO';
        $migration = $r->is_migration == 'Y' ? 'SI' : 'NO';
        $client = $r->client_name . ' ' . $r->client_lname;
        $billing = !empty($r->billing) ? $r->billing : 'No Facturado';
        $installer_email = !empty($r->installer_email) ? $r->installer_email : 'N/A';
        $zone_name = !empty($r->zone_name) ? $r->zone_name : 'N/A';

        $reportxls[] = [
          $r->unique_transaction,
          $folio,
          $r->date_reg,
          $conc,
          $seller_name,
          $r->article,
          $r->msisdn,
          $migration,
          $type_sale,
          $r->imei,
          $r->iccid,
          $r->service,
          $client,
          $phone,
          $phone2,
          $zone_name,
          $amount,
          $conciliation,
          $lat,
          $lng,
          $billing,
          $installer_name,
          $installer_email];
      }
    }
  }

  public static function getArrayResgisterReport($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {
        if ($r->sale_type == 'F') {
          $seller_name = !empty($r->sellerf_name) ? ($r->sellerf_name . ' ' . $r->sellerf_last_name) : 'N/A';
        } else {
          $seller_name = !empty($r->user_name) ? ($r->user_name . ' ' . $r->user_last_name) : 'N/A';
        }
        $coord_name = !empty($r->coord_name) ? ($r->coord_name . ' ' . $r->coord_last_name) : $seller_name;
        $installer_name = !empty($r->installer_name) ? ($r->installer_name . ' ' . $r->installer_last_name) : 'N/A';

        switch ($r->sale_type) {
          case 'T':$type_sale = 'Telefonía';
            break;
          case 'M':$type_sale = 'MIFI Nacional';
            break;
          case 'MH':$type_sale = 'MIFI Altan';
            break;
          case 'F':$type_sale = 'Fibra';
            break;
          default:$type_sale = 'Internet Hogar';
            break;
        }

        $phone = !empty($r->client_phone) ? $r->client_phone : 'N/A';
        $phone2 = !empty($r->client_phone2) ? $r->client_phone2 : 'N/A';
        $business_name = !empty($r->business_name) ? $r->business_name : 'N/A';
        $amount = number_format($r->amount, 2, '.', ',');
        $type_buy = $r->type_buy == 'CO' ? 'Contado' : 'Credito';
        $conciliation = $r->conciliation == 'Y' ? 'SI' : 'NO';
        $migration = $r->is_migration == 'Y' ? 'SI' : 'NO';
        $lat = !empty($r->lat) ? $r->lat : 'N/A';
        $lng = !empty($r->lng) ? $r->lng : 'N/A';
        $client_name = $r->client_name . ' ' . $r->client_lname;
        $billing = !empty($r->billing) ? $r->billing : 'No Facturado';
        $origin = !empty($r->from) && $r->from == 'A' ? 'API' : 'Seller';

        if ($r->sale_type == 'F') {
          $seller_email = !empty($r->sellerf_email) ? $r->sellerf_email : 'N/A';
        } else {
          $seller_email = !empty($r->user_email) ? $r->user_email : 'N/A';
        }

        $coord_email = !empty($r->coord_email) ? $r->coord_email : $seller_email;
        $installer_email = !empty($r->installer_email) ? $r->installer_email : 'N/A';
        $coord_block = (!empty($r->user_locked) && $r->user_locked == 'Y') ? 'Si' : 'No';
        $typePayment = !empty($r->typePayment) ? $r->typePayment : 'S/I';
        $zone_name = !empty($r->zone_name) ? $r->zone_name : 'N/A';
        $redsocial = 'N/A';

        if (!empty($r->campaign)) {
          $redsocial = $r->campaign;
        }

        $reportxls[] = [
          $r->unique_transaction,
          $r->date_reg,
          $business_name,
          $seller_name,
          $coord_name,
          $r->pack,
          $r->article,
          $r->msisdn,
          $migration,
          $type_sale,
          $r->imei,
          $r->iccid,
          $r->service,
          $client_name,
          $phone,
          $phone2,
          $zone_name,
          $amount,
          $type_buy,
          $conciliation,
          $lat,
          $lng,
          $billing,
          $redsocial,
          $origin,
          $seller_email,
          $coord_email,
          $coord_block,
          $installer_name,
          $installer_email,
          $typePayment];
      }
    }
  }

  public static function getArrayConcentratorReport($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {
        switch ($r->sale_type) {
          case 'T':$type_sale = 'Telefonía';
            break;
          case 'M':$type_sale = 'MIFI Nacional';
            break;
          case 'MH':$type_sale = 'MIFI Altan';
            break;
          case 'F':$type_sale = 'Fibra';
            break;
          default:$type_sale = 'Internet Hogar';
            break;
        }
        $type = $r->type == 'P' ? 'Alta' : 'Recarga';
        $pack = $r->type == 'P' ? $r->pack : 'N/A';
        $artic = $r->type == 'P' ? $r->article : 'N/A';
        $conc = !empty($r->concentrator) ? $r->concentrator : 'N/A';
        $amount = number_format($r->amount, 2, '.', ',');

        $reportxls[] = [
          $type,
          $pack,
          $artic,
          $r->service,
          $r->unique_transaction,
          $conc,
          $r->date_reg,
          $r->msisdn,
          $type_sale,
          $amount,
          $r->conciliation];
      }
    }
  }

  public static function getArrayClientsReport($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {
        $client_name = $r->name . ' ' . $r->last_name;

        switch ($r->dn_type) {
          case 'T':$type_line = 'Telefonía';
            break;
          case 'M':$type_line = 'MIFI';
            break;
          case 'MH':$type_line = 'MIFI Altan';
            break;
          case 'F':$type_line = 'Fibra';
            break;
          default:$type_line = 'Internet Hogar';
            break;
        }

        $speed = !empty($r->speed) ? $r->speed : 'N/A';
        $phone = !empty($r->phone_home) ? $r->phone_home : 'N/A';
        $typePayment = !empty($r->typePayment) ? $r->typePayment : 'S/I';

        $reportxls[] = [
          $r->client_date,
          $r->prospect_date,
          $client_name,
          $r->email,
          $r->msisdn,
          $type_line,
          $phone,
          $r->address,
          $r->service,
          $speed,
          $typePayment];
      }
    }
  }

  public static function getArrayProspectReport($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {
        $seller_name = $r->name . ' ' . $r->last_name;
        $coord_name = !empty($r->name_coord) ? $r->name_coord . ' ' . $r->last_name_coord : 'N/A';
        $email = !empty($r->email) ? $r->email : 'N/A';
        $phone = !empty($r->phone_home) ? $r->phone_home : 'N/A';
        $address = !empty($r->address) ? $r->address : 'N/A';
        $note = !empty($r->note) ? $r->note : 'N/A';
        $cdate = !empty($r->contact_date) ? $r->contact_date : 'N/A';
        $business_name = !empty($r->business_name) ? $r->business_name : 'N/A';
        $campaign = !empty($r->campaign) ? $r->campaign : 'N/A';

        $reportxls[] = [
          $r->date_reg,
          $r->name . ' ' . $r->last_name,
          $email,
          $phone,
          $address,
          $note,
          $cdate,
          $seller_name,
          $coord_name,
          $business_name,
          $campaign];
      }
    }
  }

  public function getArrayArticNotActiveReport($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {
        $seller_name = $r->name . ' ' . $r->last_name;
        $reportxls[] = [
          $r->id,
          $r->title,
          $r->msisdn,
          $r->imei,
          $seller_name,
          $r->date_reg];
      }
    }
  }

  public static function getArraySaleArticNotActiveReport($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {
        $seller_name = $r->name . ' ' . $r->last_name;
        $coord_name = !empty($r->namecoo) ? $r->namecoo . ' ' . $r->lastnamecoo : $seller_name;

        $reportxls[] = [
          $r->unique_transaction,
          $r->msisdn,
          $r->title,
          $r->service,
          $seller_name,
          $coord_name,
          $r->business_name,
          $r->date_reg];
      }
    }
  }

  public static function getArrayArticActiveReport($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {
        $seller_name = $r->name . ' ' . $r->last_name;
        $coord_name = !empty($r->namecoo) ? $r->namecoo . ' ' . $r->lastnamecoo : $seller_name;
        $phone = !empty($r->phone_home) ? $r->phone_home : 'N/A';
        $phone2 = !empty($r->phone) ? $r->phone : 'N/A';
        $email = !empty($r->email) ? $r->email : 'N/A';
        $date_sale = !empty($r->date_sale) ? $r->date_sale : $r->date_reg;

        $reportxls[] = [
          $r->unique_transaction,
          $r->cliname . ' ' . $r->clilastname,
          $phone,
          $phone2,
          $email,
          $r->msisdn,
          $r->title,
          $r->service,
          $seller_name,
          $coord_name,
          $r->business_name,
          $r->date_reg,
          $date_sale];
      }
    }
  }

  public static function getArrayFinancingClientReport($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {
        $date_reg = date("d-m-Y H:i:s", strtotime($r->date_reg));
        $amountFinancing = number_format($r->amount_financing, 2, '.', ',');
        $amount = number_format($r->total_amount, 2, '.', ',');
        $dues = $r->num_dues == 0 ? '0' : $r->num_dues;
        $pay = number_format($r->pay, 2, '.', ',');
        $remaining = number_format($r->price_remaining, 2, '.', ',');

        $reportxls[] = [
          $r->msisdn,
          $date_reg,
          $amountFinancing,
          $amount,
          $dues,
          $pay,
          $remaining];
      }
    }
  }

  public static function getArrayConcilationsReport($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {
        $ope = $r->ope_name . ' ' . $r->ope_last_name;
        $name = $r->name . ' ' . $r->last_name;
        $sup = !empty($r->sup_name) ? ($r->sup_name . ' ' . $r->sup_last_name) : 'N/A';
        $mot = !empty($r->reason_deposit) ? $r->reason_deposit : 'N/A';
        $bank = !empty($r->bank) ? $r->bank : 'Otro';

        $reportxls[] = [
          $r->id,
          $r->amount,
          $bank,
          $ope,
          $name,
          $sup,
          $r->id_deposit,
          $r->date_process,
          $mot];
      }
    }
  }

  public static function getArrayOnlineAPIReport($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {
        $name = $r->name . ' ' . $r->last_name;
        $phone = !empty($r->phone_home) ? $r->phone_home : 'N/A';
        $email = !empty($r->email) ? $r->email : 'N/A';
        $invo = $r->require_invoice == 'Y' ? 'Si' : 'No';
        $rfc = !empty($r->rfc) ? $r->rfc : $r->dni;
        $msisdn = !empty($r->msisdn) ? $r->msisdn : 'N/A';
        $date = !empty($r->date) ? date("d-m-Y", strtotime($r->date)) : 'N/A';
        $order99 = !empty($r->order99) ? $r->order99 : 'N/A';
        $desc = !empty($r->description) ? strtolower($r->description) : 'N/A';
        $pdf = !empty($r->url_pdf) ? $r->url_pdf : 'N/A';
        $status = !empty($r->status_dn) ? $r->status_dn : 'N/A';
        $amount_del = '$' . number_format($r->amount_del, 2, '.', ',');
        $price = '$' . number_format($r->price_pack, 2, '.', ',');

        $reportxls[] = [
          $r->transaction,
          $name,
          $phone,
          $email,
          $invo,
          $rfc,
          $msisdn,
          $r->title,
          $date,
          $r->order,
          $order99,
          $desc,
          $pdf,
          $status,
          $amount_del,
          $price];
      }
    }
  }

  public static function getArrayConsumoReport($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {
        $cons = 'S/I';
        $nof = 'S/I';
        $de = 'S/I';
        $days = 'S/I';
        $type = 'Recarga';

        if (!empty($r->consuption)) {
          $cons = (String) round(((($r->consuption / 1024) / 1024) / 1024), 2);
        }

        if (!empty($r->offer_name)) {
          $nof = $r->offer_name;
        }

        if (!empty($r->date_sup_en)) {
          $de = Carbon::createFromFormat(
            'Y-m-d',
            $r->date_sup_en
          )
            ->format('Y-m-d');
        }

        if (!empty($r->days)) {
          $days = (String) $r->days;
        }

        if ($r->type == 'P') {
          $type = 'Alta';
        }

        $db = Carbon::createFromFormat('Y-m-d H:i:s', $r->date_reg)
          ->format('Y-m-d');

        $reportxls[] = [
          $r->msisdn,
          $cons,
          $r->title,
          $r->codeAltan,
          $nof,
          $db,
          $de,
          $days,
          $type];
      }
    }
  }

  public static function getArrayConsumoCDRReport($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {
        $cons = '0';
        $thrt = '0';
        $de = 'S/I';
        $days = '0';
        $type = 'Recarga';

        if (!empty($r->consuption)) {
          $cons = (String) round(((($r->consuption / 1024) / 1024) / 1024), 2);
        }

        if (!empty($r->throttling)) {
          $thrt = (String) round(((($r->throttling / 1024) / 1024) / 1024), 2);
        }

        $de = !empty($r->date_sup_en) ? Carbon::createFromFormat(
          'Y-m-d',
          $r->date_sup_en
        )
          ->format('Y-m-d') :
        Carbon::createFromFormat(
          'Y-m-d H:i:s',
          $r->date_reg_rec
        )
          ->addDays($r->period + 1)
          ->format('Y-m-d');

        if (!empty($r->days)) {
          $days = (String) $r->days;
        }

        if ($r->type == 'P') {
          $type = 'Alta';
        }
        if ($r->type == 'SR') {
          $type = 'Retención';
        }

        $db = Carbon::createFromFormat(
          'Y-m-d H:i:s',
          !empty($r->date_reg) ? $r->date_reg : $r->date_reg_rec
        )
          ->format('Y-m-d');

        $reportxls[] = [
          $r->msisdn,
          $cons,
          $thrt,
          $r->title,
          $r->codeAltan,
          $db,
          $de,
          $days,
          $type];
      }
    }
  }

  public static function getArrayInstSalesReport($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {
        $seller = $r->name_seller . ' ' . $r->last_name_seller;
        $coord = $r->name_coord . ' ' . $r->last_name_coord;
        $imei = !empty($r->imei) ? $r->imei : 'S/I';
        $client = $r->name_client . ' ' . $r->last_name_client;
        $phone = !empty($r->phone_home) ? $r->phone_home : 'N/A';

        $dsale = 'N/A';
        if (!empty($r->date_reg_alt)) {
          $dsale = Carbon::createFromFormat('Y-m-d H:i:s', $r->date_reg_alt)
            ->format('d-m-Y H:i:s');
        }

        $dnq = $r->date_expired;
        if ($dnq != 'N/A') {
          $dnq = Carbon::createFromFormat('d-m-Y', $dnq)
            ->format('d-m-Y');
        }

        $status = $r->expired ? 'Vencida' : 'Al día';
        $quote = $r->quotes . '/' . $r->total_quotes;
        $amount = '$' . number_format($r->amount, 2, '.', ',');

        switch ($r->artic_type) {
          case 'T':$tl = 'Telefonía';
            break;
          case 'M':$tl = 'MIFI';
            break;
          case 'MH':$tl = 'MIFI Altan';
            break;
          case 'F':$tl = 'Fibra';
            break;
          default:$tl = 'Internet Hogar';
            break;
        }

        $reportxls[] = [
          $r->unique_transaction,
          $r->business_name,
          $seller,
          $coord,
          $r->pack,
          $r->product,
          $r->msisdn,
          $tl,
          $imei,
          $r->service,
          $client,
          $phone,
          $dsale,
          $dnq,
          $status,
          $quote,
          $amount];
      }
    }
  }

  public static function getArrayMigrationsReport($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {
        $artic_type = 'Hogar';

        switch ($r->artic_type) {
          case 'H':$artic_type = 'Hogar';
            break;
          case 'T':$artic_type = 'Telefonia';
            break;
          case 'M':$artic_type = 'MIFI';
            break;
          default:$artic_type = 'Hogar';
            break;
        }

        $reportxls[] = [
          $r->client,
          $r->msisdn_old,
          $r->alta_old,
          $r->vendor_old,
          $r->last_recharge,
          $r->imei_code,
          $artic_type,
          $r->msisdn_new,
          $r->date_migration,
          $r->vendor_new,
          $r->pack];
      }
    }
  }

  public static function getArrayRechargeBase($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {
        $name = $r->name . ' ' . $r->last_name;
        $phone = !empty($r->phone_home) ? $r->phone_home : 'N/A';
        $phone2 = !empty($r->phone) ? $r->phone : 'N/A';
        $email = !empty($r->email) ? $r->email : 'N/A';
        $date = date("d-m-Y", strtotime($r->date_reg));

        $reportxls[] = [
          $r->msisdn,
          $name,
          $phone,
          $phone2,
          $email,
          $r->dni,
          $date];
      }
    }
  }

  public static function getArrayActiveBase($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {
        $name = $r->name . ' ' . $r->last_name;
        $phone = !empty($r->phone_home) ? $r->phone_home : 'N/A';
        $phone2 = !empty($r->phone) ? $r->phone : 'N/A';
        $email = !empty($r->email) ? $r->email : 'N/A';
        $date_eve = !empty($r->date_event) ? date("d-m-Y", strtotime($r->date_event)) : 'N/A';
        $answer = (empty($r->answer) || $r->answer == 'N') ? 'No' : 'Si';
        $acept = (empty($r->acept) || $r->acept == 'N') ? 'No' : 'Si';
        $commnet = empty($r->comment) ? 'N/A' : $r->comment;
        $call_date = empty($r->date_call) ? 'N/A' : date("d-m-Y H:i", strtotime($r->date_call));

        $reportxls[] = [
          $r->msisdn,
          $name,
          $phone,
          $phone2,
          $email,
          $r->dni,
          $date_eve,
          $answer,
          $acept,
          $commnet,
          $call_date];
      }
    }
  }

  public static function getArrayChurn90($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {
        $name = $r->name . ' ' . $r->last_name;
        $phone = !empty($r->phone_home) ? $r->phone_home : 'N/A';
        $phone2 = !empty($r->phone) ? $r->phone : 'N/A';
        $email = !empty($r->email) ? $r->email : 'N/A';
        $date_eve = !empty($r->date_event) ? date("d-m-Y", strtotime($r->date_event)) : 'N/A';
        $answer = (empty($r->answer) || $r->answer == 'N') ? 'No' : 'Si';
        $acept = (empty($r->acept) || $r->acept == 'N') ? 'No' : 'Si';
        $commnet = empty($r->comment) ? 'N/A' : $r->comment;
        $call_date = empty($r->date_call) ? 'N/A' : date("d-m-Y H:i", strtotime($r->date_call));

        $reportxls[] = [
          $r->msisdn,
          $name,
          $phone,
          $phone2,
          $email,
          $r->dni,
          $date_eve,
          $answer,
          $acept,
          $commnet,
          $call_date];
      }
    }
  }

  public static function getArrayDecay90($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {
        $name = $r->name . ' ' . $r->last_name;
        $phone = !empty($r->phone_home) ? $r->phone_home : 'N/A';
        $phone2 = !empty($r->phone) ? $r->phone : 'N/A';
        $email = !empty($r->email) ? $r->email : 'N/A';
        $date_eve = !empty($r->date_event) ? date("d-m-Y", strtotime($r->date_event)) : 'N/A';
        $answer = (empty($r->answer) || $r->answer == 'N') ? 'No' : 'Si';
        $acept = (empty($r->acept) || $r->acept == 'N') ? 'No' : 'Si';
        $commnet = empty($r->comment) ? 'N/A' : $r->comment;
        $call_date = empty($r->date_call) ? 'N/A' : date("d-m-Y H:i", strtotime($r->date_call));

        $reportxls[] = [
          $r->msisdn,
          $name,
          $phone,
          $phone2,
          $email,
          $r->dni,
          $date_eve,
          $answer,
          $acept,
          $commnet,
          $call_date];
      }
    }
  }

  public static function getArrayPayjoy($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {
        $coord = !empty($r->coord_name) ? $r->coord_name . ' ' . $r->coord_last_name : 'N/A';
        $seller = !empty($r->seller_name) ? $r->seller_name . ' ' . $r->seller_last_name : 'N/A';
        $client = !empty($r->client_name) ? $r->client_name . ' ' . $r->client_last_name : 'N/A';
        $amount = '$' . round($r->amount, 2);
        $amountF = '$' . round($r->total_amount, 2);
        $initAmount = '$' . round(($r->total_amount - $r->amount), 2);
        $dateF = date('d-m-Y H:i:s', strtotime($r->date_reg));
        $dateA = !empty($r->date_process) ? date('d-m-Y H:i:s', strtotime($r->date_process)) : 'N/A';
        $status = $r->status == 'A' ? 'Notificado' : 'Asociado';

        $reportxls[] = [
          $r->msisdn,
          $coord,
          $seller,
          $client,
          $initAmount,
          $amount,
          $amountF,
          $dateF,
          $dateA,
          $status];
      }
    }
  }

  public static function getArrayCoordinates($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {
        $client = $r->client_name . ' ' . $r->client_last_name;
        $user = $r->user_name . ' ' . $r->user_last_name;
        $dateReg = date('d-m-Y H:i:s', strtotime($r->date_reg));

        $reportxls[] = [
          $r->dn,
          $client,
          $r->phone_home,
          $user,
          $r->user_email,
          $r->old_lat,
          $r->old_lng,
          $r->new_lat,
          $r->new_lng,
          $dateReg];
      }
    }
  }

  public static function getArrayNominaVoywey($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {

        $dateReg = date('d-m-Y H:i:s', strtotime($r->date_reg));
        $dateDelive = !empty($r->date_del) ? date('d-m-Y H:i:s', strtotime($r->date_del)) : '';
        $msisdn = !empty($r->DN) ? $r->DN : 'S/N';
        $MP_transaction = !empty($r->MP_transaction) ? $r->MP_transaction : 'N/A';

        $reportxls_new = array(
          'Folio' => $r->folio,
          'Nombre del vendedor' => $r->nameUser,
          'Apellido del vendedor' => $r->lastNameUser,
          'Email del vendedor' => $r->seller,
          'Nombre del repartidor' => $r->name,
          'Apellido del repartidor' => $r->last_name,
          'Email del repartidor' => $r->email,
          'Telefono del repartidor' => $r->phone,
          'DNI del repartidor' => $r->dni,
          'Direccion de entrega' => $r->address_dest,
          'Direccion de activacion' => $r->address_active,
          'Precio' => $r->total,
          'Forma de pago' => $r->payment_method,
          'Numero de transacion' => $MP_transaction,
          'Nombre del cliente' => $r->client_name,
          'Apellido del cliente' => $r->client_last_name,
          'Email del cliente' => $r->client_email,
          'Telefono del cliente' => $r->client_phone,
          'Fecha de registro' => $dateReg,
          'Fecha de entrega' => $dateDelive,
          'MSISDN activado' => $msisdn,
        );
        array_push($reportxls, $reportxls_new);
      }
    }
  }

  public static function getArrayConciliacionVoywey($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {

        $dateReg = date('d-m-Y H:i:s', strtotime($r->date_reg));
        $dateDelive = !empty($r->date_del) ? date('d-m-Y H:i:s', strtotime($r->date_del)) : '';
        $reportxls_new = array(
          'Folio' => $r->folio,
          'Nombre del vendedor' => $r->nameUser,
          'Apellido del vendedor' => $r->lastNameUser,
          'Email del vendedor' => $r->seller,
          'Nombre del repartidor' => $r->name,
          'Apellido del repartidor' => $r->last_name,
          'Email del repartidor' => $r->email,
          'Telefono del repartidor' => $r->phone,
          'DNI del repartidor' => $r->dni,
          'Direccion de entrega' => $r->address_dest,
          'Direccion de activacion' => $r->address_active,
          'Deuda' => $r->total,
          'Forma de pago' => $r->payment_method,
          'Dias de deuda' => $r->hrs_desde_entrega,
          'Nombre del cliente' => $r->client_name,
          'Apellido del cliente' => $r->client_last_name,
          'Email del cliente' => $r->client_email,
          'Telefono del cliente' => $r->client_phone,
          'Fecha de registro' => $dateReg,
          'Fecha de entrega' => $dateDelive,
        );
        array_push($reportxls, $reportxls_new);
      }
    }
  }

  public static function getArrayInventaryVoywey($collection = false, &$reportxls = [])
  {
    if ($collection) {

      foreach ($collection as $r) {
        $reportxls_new = array(
          'id_bodega' => $r['id_bodega'],
          'name' => $r['name'],
          'disp_bodega' => $r['disp_bodega'],
          'asignados' => $r['asignados'],
          'en_camino' => $r['en_camino'],
          'deliveryName' => $r['deliveryName'],
          'deliveryLastName' => $r['deliveryLastName'],
          'deliveryEmail' => $r['deliveryEmail'],
          'sku' => $r['sku'],
          'nameProduct' => $r['nameProduct'],
          'dn' => $r['dn'],
          'estatus' => $r['estatus'],
        );
        array_push($reportxls, $reportxls_new);
      }
    }
  }

  public static function getArraySalesJelouVoywey($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {

        $Fecha = date('d-m-Y H:i:s', strtotime($r->Fecha));
        $Fecha_Activacion = date('d-m-Y H:i:s', strtotime($r->Fecha_Activacion));
        $Codigo = !empty($r->Codigo) ? $r->Codigo : 'N/A';
        $FormaPago = ($r->FormaPago == 'CASH') ? 'Efectivo' : 'Tarjeta';

        if (!empty($Fecha_Activacion) && $r->status_client == 'A') {
          $Fecha_Activacion = $Fecha_Activacion != 0 ? $Fecha_Activacion : '0';
        } else {
          $Fecha_Activacion = 'N/A';
        }
        if ($r->status_client == 'A') {

          $Dias_en_Activar = $r->Dias_en_Activar != 0 ? $r->Dias_en_Activar . ' dia(s)' : '0 dias';

        } else {
          $date1 = date_create(date("Y-m-d", strtotime($Fecha)));
          $date2 = date_create(date("Y-m-d"));

          $resultado = $date1->diff($date2);
          $Dias_en_Activar = $resultado->format('%a dia(s)');
        }
        $ClienteMail = !empty($r->ClienteMail) ? $r->ClienteMail : 'N/A';
        $MSISDN = !empty($r->MSISDN) ? $r->MSISDN : 'N/A';

        switch ($r->status) {
          case 'P':
            $status = 'En transito';
            break;
          case 'A':
            $status = 'Pendiente de deposito';
            break;
          case 'D':
            $status = 'Rechazado';
            break;
          case 'T':
            $status = 'Eliminado';
            break;
          case 'C':
            $status = 'Orden finalizada';
            break;
          default:
            $status = 'No disponible';
            break;
        }

        $reportxls_new = array(
          'Orden' => $r['Orden'],
          'OrderVoy' => $r['OrderVoy'],
          'status' => $status,
          'Fecha' => $Fecha,
          'Dias_en_Activar' => $Dias_en_Activar,
          'Fecha_Activacion' => $Fecha_Activacion,
          'Monto' => $r['Monto'],
          'Codigo' => $Codigo,
          'FormaPago' => $FormaPago,
          'UserMail' => $r['UserMail'],
          'UserName' => $r['UserName'],
          'Userlastname' => $r['Userlastname'],
          'Userphone' => $r['Userphone'],
          'Repartidorine' => $r['Repartidorine'],
          'Repartidor_name' => $r['Repartidor_name'],
          'Repartidor_lastname' => $r['Repartidor_lastname'],
          'Repartidor_mail' => $r['Repartidor_mail'],
          'Repartidor_phone' => $r['Repartidor_phone'],
          'DNI' => $r['DNI'],
          'ClientName' => $r['ClientName'],
          'ClientLastName' => $r['ClientLastName'],
          'ClienteMail' => $ClienteMail,
          'MSISDN' => $MSISDN,
          'Modelo' => $r['Modelo'],
          'Full_plan' => $r['Full_plan'],
        );
        array_push($reportxls, $reportxls_new);
      }
    }
  }

  public static function getArrayUserLockedReport($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {
        $name_user = !empty($r->name_user) ? $r->name_user . ' ' . $r->last_name_user : 'N/A';

        $name_user = strtoupper($name_user);

        $name_dolockuser = !empty($r->name_dolockuser) ? $r->name_dolockuser . ' ' . $r->last_name_dolockuser : 'N/A';
        $name_dolockuser = strtoupper($name_dolockuser);

        $name_dounlockuser = !empty($r->name_dounlockuser) ? $r->name_dounlockuser . ' ' . $r->last_name_dounlockuser : 'N/A';
        $name_dounlockuser = strtoupper($name_dounlockuser);

        $date_locked = !empty($r->date_locked) ? date('d-m-Y H:i:s', strtotime($r->date_locked)) : 'N/A';

        $date_unlocked = !empty($r->date_unlocked) ? date('d-m-Y H:i:s', strtotime($r->date_unlocked)) : 'N/A';

        $days = 'N/A';
        if (!empty($r->date_unlocked)) {
          $dateb = Carbon::createFromFormat('Y-m-d H:i:s', $r->date_unlocked);
          $datea = Carbon::createFromFormat('Y-m-d H:i:s', $r->date_locked);

          $days = (String) $dateb->diffInDays($datea);
        }

        $reportxls[] = [
          $name_user,
          $name_dolockuser,
          $name_dounlockuser,
          $date_locked,
          $date_unlocked,
          $days];
      }
    }
  }

  public static function getArraySuperSim($collection = false, &$reportxls = [])
  {
    if ($collection) {

      foreach ($collection as $r) {

        $mailClient = !empty($r['mailClient']) ? $r['mailClient'] : 'S/N';
        $nameVendedor = !empty($r['nameVendedor']) ? $r['nameVendedor'] : $r['id_point'];
        $mailvendedor = !empty($r['mailvendedor']) ? $r['mailvendedor'] : 'S/N';

        $reportxls_new = array(
          'msisdn' => $r['msisdn'],
          'nameClient' => $r['nameClient'],
          'mailClient' => $mailClient,
          'nameVendedor' => $nameVendedor,
          'mailvendedor' => $mailvendedor,
          'amount' => $r['amount'],
          'servicio' => $r['servicio'],
          'rownum_sales' => $r['rownum_sales'],
          'date_reg' => $r['date_reg'],
          'id' => $r['id'],
        );
        array_push($reportxls, $reportxls_new);
      }
    }
  }

  public static function getArrayPortImportPeriodo($collection = false, &$reportxls = [])
  {
    if ($collection) {

      foreach ($collection as $r) {

        $date_reg = !empty($r->date_reg) ? date("d-m-Y H:i:s", strtotime($r->date_reg)) : 'S/N';
        switch ($r->status) {
          case 'A':$status = 'Activo';
            break;
          case 'C':$status = 'Cancelado';
            break;
          case 'P':$status = 'Procesado';
            break;
          case 'S':$status = 'Solicitud Netwey';
            break;
          case 'W':$status = 'En proceso Netwey';
            break;
          case 'E':$status = 'Error';
            break;
          case 'SS':$status = 'En proceso ADB';
            break;
          case 'IS':$status = 'Incidencia ADB';
            break;
          case 'SA':$status = 'En proceso Altan';
            break;
          default:$status = 'Desconocido';
            break;
        }

        $Observation = !empty($r->Observation) ? $r->Observation : 'S/N';
        $details_error = !empty($r->details_error) ? $r->details_error : 'S/N';
        $portID = !empty($r->portID) ? $r->portID : 'S/N';

        $reportxls[] = [
          $r->sale_id,
          $r->msisdn_user,
          $r->msisdn_netwey,
          $r->nip,
          $date_reg,
          $r->date_process,
          $status,
          $Observation,
          $details_error,
          $portID];
      }
    }
  }

  public static function getArrayPortExportPeriodo($collection = false, &$reportxls = [])
  {
    if ($collection) {

      foreach ($collection as $r) {
        $dni_client = !empty($r->dni_client) ? $r->dni_client : 'S/N';
        $sales_id = !empty($r->sales_id) ? $r->sales_id : 'S/N';
        $sales_date = !empty($r->sales_date) ? $r->sales_date : 'S/N';
        $NameClient = !empty($r->NameClient) ? $r->NameClient : 'S/N';

        switch ($r->status) {
          case "PN":
            $status = 'Procesado Netwey';
            break;
          case "PA":
            $status = 'Procesado Altan';
            break;
          case "C":
            $status = 'Notificado por ADB';
            break;
          case "E":
            $status = 'Error';
            break;
        }

        $reportxls[] = [
          $r->msisdn,
          $sales_id,
          $sales_date,
          $r->port_date,
          $r->portID,
          $dni_client,
          $NameClient,
          $status,
          $r->result];
      }
    }
  }

  public static function getArraySalesAPIReport($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {
        $seller = $r->name . ' ' . $r->last_name;

        $typeL = 'HBB';
        if ($r->pack_type == 'M') {
          $typeL = 'MIFI';
        }
        if ($r->pack_type == 'MH') {
          $typeL = 'Mifi huella alatan';
        }
        if ($r->pack_type == 'T') {
          $typeL = 'Telefonía';
        }
        if ($r->pack_type == 'F') {
          $typeL = 'Fibra';
        }

        $msisdn = !empty($r->msisdn) ? $r->msisdn : 'N/A';

        $client = $r->name_client . ' ' . $r->last_name_client;

        $amountp = '$' . number_format($r->sub_monto, 2, '.', ',');

        $logic = 'N/F';
        if (!empty($r->folio_99)) {
          $logic = '99min';
        }
        if (!empty($r->folio_voy)) {
          $logic = 'Voywey';
        }
        if (!empty($r->folio_pro)) {
          $logic = 'Prova';
        }

        $amountDel = '$' . number_format($r->monto_envio, 2, '.', ',');

        $logicG = 'N/F';
        if (!empty($r->folio_99)) {
          $logicG = $r->folio_99;
        }
        if (!empty($r->folio_voy)) {
          $logicG = $r->folio_voy;
        }
        if (!empty($r->folio_pro)) {
          $logicG = $r->folio_pro;
        }

        $codProm = !empty($r->cod_prom) ? $r->cod_prom : 'N/A';

        $discount = '$' . number_format($r->discount, 2, '.', ',');

        $saleDate = date("d-m-Y", strtotime($r->sale_date));

        $delDate = !empty($r->del_date) ? date("d-m-Y", strtotime($r->del_date)) : 'N/A';

        $activeDays = 'N/A';
        if ($r->status == 'A') {
          $activeDays = $r->active_days;
        }

        $status = 'Generada';
        if ($r->status == 'A') {
          $status = 'Finalizada';
        }
        if ($r->status == 'I') {
          $status = 'Entregada';
        }

        $cp = 'N/A';
        if (!empty($r->postal_code_99)) {
          $cp = $r->postal_code_99;
        }
        if (!empty($r->postal_code_v)) {
          $cp = $r->postal_code_v;
        }

        $state = 'N/A';
        if (!empty($r->state_99)) {
          $state = $r->state_99;
        }
        if (!empty($r->state_v)) {
          $state = $r->state_v;
        }

        $city = 'N/A';
        if (!empty($r->city_99)) {
          $city = $r->city_99;
        }
        if (!empty($r->city_v)) {
          $city = $r->city_v;
        }

        $date_status = 'N/A';
        if (!empty($r->date_status)) {
          $date_status = date("d-m-Y", strtotime($r->date_status));
        }

        $last_status = 'N/A';
        if (!empty($r->last_status)) {
          $last_status = $r->last_status;
        }

        $reportxls[] = [
          $r->transaction,
          $r->business_name,
          $seller,
          $r->product,
          $typeL,
          $r->title,
          $r->service,
          $msisdn,
          $client,
          $r->phone_home,
          $r->email_client,
          $amountp,
          $logic,
          $last_status,
          $date_status,
          $amountDel,
          $logicG,
          $cp,
          $state,
          $city,
          $codProm,
          $discount,
          $saleDate,
          $delDate,
          $activeDays,
          $status];
      }
    }
  }

  public static function getArrayUpsWithConsumptionsReport($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {

        $consumo = round(($r->Consumo / 1024 / 1024), 2);

        $reportxls[] = [
          $r->msisdn,
          $r->Fecha_Alta,
          $r->Fecha_Consumo,
          $consumo];

      }
    }
  }

  public static function getArraySuspendedHistory($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {
        $reportxls[] = [
          $r->msisdn,
          $r->client,
          $r->typesuspended,
          $r->date_reg];

      }
    }
  }

  public static function getArrayCoppelSales($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {
        $reportxls[] = [
          $r->msisdn,
          $r->seller_name,
          $r->client_name,
          $r->client_phone,
          $r->amount,
          $r->date_reg,
          $r->pack,
          $r->article,
          $r->status];

      }
    }
  }
  public static function getArrayJelouSales($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {

        switch ($r->statusDN) {
          case 'A':$statusDN = 'Activo';
            break;
          case 'I':$statusDN = 'Inactivo';
            break;
          case 'S':$statusDN = 'Suspendido';
            break;
          case 'S/N':$statusDN = 'S/N';
            break;
          case 'Por activar...':$statusDN = 'Por activar...';
            break;
          default:$status = 'Status desconocido';
            break;
        }
        switch ($r->typeDN) {
          case 'H':
            $typeDN = "HBB";
            break;
          case 'M':
            $typeDN = "Mifi";
            break;
          case 'MH':
            $typeDN = "Mifi Huella";
            break;
          default:
            $typeDN = "Tipo desconocido";
            break;
        }

        if (!empty($r->release_date)) {
          if ($r->release_date != 'S/N' && $r->release_date != 'En camino...' && $r->release_date != 'Por activar...') {
            $release_date = date_format(date_create($r->release_date), "d-m-Y H:i:s");
          } else {
            $release_date = $r->release_date;
          }
        } else {
          $release_date = 'N/A';
        }

        if (!empty($r->date_conciliado)) {
          if ($r->date_conciliado != 'S/N' && $r->date_conciliado != 'En camino...' && $r->date_conciliado != 'Por activar...') {
            $date_conciliado = date_format(date_create($r->date_conciliado), "d-m-Y H:i:s");
          } else {
            $date_conciliado = $r->date_conciliado;
          }
        } else {
          $date_conciliado = 'N/A';
        }

        if (!empty($r->date_sales)) {
          if ($r->date_sales != 'S/N' && $r->date_sales != 'En camino...' && $r->date_sales != 'Por activar...') {
            $date_sales = date_format(date_create($r->date_sales), "d-m-Y H:i:s");
          } else {
            $date_sales = $r->date_sales;
          }
        } else {
          $date_sales = 'N/A';
        }

        if (!empty($r->date_delivery)) {
          if ($r->date_delivery != 'S/N' && $r->date_delivery != 'En camino...' && $r->date_delivery != 'Por activar...') {
            $date_delivery = date_format(date_create($r->date_delivery), "d-m-Y H:i:s");
          } else {
            $date_delivery = $r->date_delivery;
          }
        } else {
          $date_delivery = 'N/A';
        }

        $reportxls[] = [
          $r->folio,
          $r->courier,
          $r->nameClient,
          !empty($r->telfClient) ? $r->telfClient : 'N/A',
          $r->dniClient,
          $r->status_ord,
          $r->days_Lastsales . " dias",
          $r->msisdn,
          $statusDN,
          $typeDN,
          $r->SKU,
          $r->operadorLogistico,
          $date_sales,
          $r->state_delivery,
          $r->address_delivery,
          $date_delivery,
          $r->mount,
          $r->type_payment,
          $r->conciliado,
          $date_conciliado,
          $release_date];
      }
    }
  }

  public static function getArrayOrderRequest($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {

        $estatus = "";
        switch ($r->status) {
          case 'A':$estatus = "Sin Procesar";
            break;
          case 'E':$estatus = "Con Error";
            break;
          case 'P':$estatus = "Asignado a Coordinador";
            break;
          case 'AS':$estatus = "Asignado a Regional";
            break;
          case 'PR':$estatus = "Reciclaje";
            break;
          case 'T':$estatus = "Eliminado";
            break;
        }

        $recicler_status = "N/A";
        if (!empty($r->recicler_status)) {
          switch ($r->recicler_status) {
            case 'C':$recicler_status = "Creado";
              break;
            case 'F':$recicler_status = "Procesado sufijo";
              break;
            case 'P':$recicler_status = "Agregado a inventario";
              break;
            case 'M':$recicler_status = "Solicitud manual";
              break;
            case 'E':$recicler_status = "Error";
              break;
            case 'R':$recicler_status = "Rechazado";
              break;
          }
        }

        if (!empty($r->last_user_action)) {
          $last_user_action = $r->last_user_action;
        } else {
          $last_user_action = "N/A";
        }

        if (!empty($r->reg_date_action)) {
          $reg_date_action = Carbon::createFromFormat('Y-m-d H:i:s', $r->reg_date_action)->format('d-m-Y H:i:s');
        } else {
          $reg_date_action = "N/A";
        }

        if (!empty($r->coo_date_action)) {
          $coo_date_action = Carbon::createFromFormat('Y-m-d H:i:s', $r->coo_date_action)->format('d-m-Y H:i:s');
        } else {
          $coo_date_action = "N/A";
        }

        if (!empty($r->comment)) {
          $comment = $r->comment;
        } else {
          $comment = "N/A";
        }

        $date_reg = Carbon::createFromFormat('Y-m-d H:i:s', $r->date_reg)->format('d-m-Y H:i:s');

        $reportxls[] = [
          $r->file,
          $r->box,
          $r->msisdn,
          $r->sku,
          $r->iccid,
          $r->imei,
          $r->branch,
          $r->folio,
          $r->user,
          $estatus,
          $recicler_status,
          $last_user_action,
          $reg_date_action,
          $coo_date_action,
          $comment,
          $date_reg];
      }
    }
  }

  public static function getArrayInventoryTracks($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {
        if (!empty($r->origin_user)) {
          $origin = $r->origin_user;
        } else {
          $origin = $r->origin_wh;
        }

        if (!empty($r->destination_user)) {
          $destination = $r->destination_user;
        } else {
          $destination = $r->destination_wh;
        }

        $reportxls[] = [
          $r->msisdn,
          $r->sku,
          $r->article,
          $r->date_reg,
          $origin,
          $destination,
          $r->assigned_by,
          $r->comment];
      }
    }
  }

  public static function getArrayInventoryMerma($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {
        $orgName = !empty($r->origin_name) ? $r->origin_name : 'S/I';
        $assiName = !empty($r->assigned_name) ? $r->assigned_name : 'S/I';
        $dateReg = 'S/I';
        if (!empty($r->date_reg)) {
          $dateReg = Carbon::createFromFormat('Y-m-d H:i:s', $r->date_reg)
            ->format('Y-m-d H:i');
        }

        $reportxls[] = [
          $r->msisdn,
          $r->title,
          $orgName,
          $assiName,
          $r->name,
          $dateReg];
      }
    }
  }

  public static function getArrayFiberInstallations($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {
        switch ($r->status) {
          case 'A':$status = 'En proceso';
            break;
          case 'R':$status = 'Reprogramado';
            break;
          case 'P':$status = 'Instalado';
            break;
        }

        switch ($r->paid) {
          case 'N':$paid = 'No';
            break;
          case 'Y':$paid = 'Si';
            break;
        }

        if (!empty($r->date_install)) {
          $date_install = $r->date_install;
        } else {
          $date_install = 'N/A';
        }

        if (!empty($r->msisdn)) {
          $msisdn = $r->msisdn;
        } else {
          $msisdn = 'N/A';
        }

        if (!empty($r->num_rescheduling)) {
          $num_rescheduling = $r->num_rescheduling;
        } else {
          $num_rescheduling = '0';
        }

        if (!empty($r->zone_name)) {
          $zone_name = $r->zone_name;
        } else {
          $zone_name = 'N/A';
        }

        $reportxls[] = [
          $r->id_proccess,
          $msisdn,
          $r->client,
          $r->client_email,
          $r->client_phone,
          $r->address_instalation,
          $r->seller,
          $r->installer,
          $r->installer_phone,
          $zone_name,
          $r->date_presell,
          $date_install,
          $paid,
          $status,
          $r->date_rescheduling,
          $num_rescheduling];
      }
    }
  }

  public static function getArrayInventoryStatus($collection = false, &$reportxls = [])
  {
    if ($collection) {

      foreach ($collection as $r) {

        if (empty($r->esquema)) {
          $esquema = 'N/A';
        } else {
          $esquema = $r->esquema;
        }

        switch ($r->artic_type) {
          case 'H':$artic_type = "Internet Hogar";
            break;
          case 'T':$artic_type = "Telefonia";
            break;
          case 'M':$artic_type = "Mifi";
            break;
          case 'F':$artic_type = "Fibra";
            break;
          default:$artic_type = "N/A";
            break;
        }

        switch ($r->color) {
          case 'red':$color = "Rojo";
            break;
          case 'orange':$color = "Naranja";
            break;
          default:$color = "N/A";
            break;
        }

        if (empty($r->evidence)) {
          $evidence = 'N/A';
        } else {
          $evidence = $r->evidence;
        }

        $date_color = !empty($r->date_color) ? date("d-m-Y H:i:s", strtotime($r->date_color)) : 'N/A';

        $reportxls[] = [
          $r->assigned,
          $r->nameAssigned,
          $esquema,
          $r->msisdn,
          $r->title,
          $artic_type,
          $evidence,
          $color,
          $date_color];
      }
    }
  }

  public static function getArrayMermaWarehouseOldEquipment($collection = false, &$reportxls = [])
  {
    if ($collection) {

      foreach ($collection as $r) {

        $reportxls[] = [
          $r->msisdn,
          $r->title,
          $r->name_supervisor,
          $r->name_seller,
          $r->first_assignment,
          $r->date_red];
      }
    }
  }

  public static function callGetArray($function = false, $collection = false, &$reportxls = [])
  {
    if ($function && $collection) {
      //Me falta validar si existe la funcion que intentan llamar
      call_user_func_array(self::class . '::' . $function, array($collection, &$reportxls));
    }

    return $reportxls;
  }

  public static function getArrayLowRequest($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {
        $date_reg = !empty($r->date_reg) ? date("d-m-Y H:i:s", strtotime($r->date_reg)) : 'S/N';
        $cash_request = !empty($r->cash_request) ? '$ ' . $r->cash_request : '$ 0';
        $days_cash_request = !empty($r->days_cash_request) ? $r->days_cash_request : '0';
        $article_request = !empty($r->article_request) ? '$ ' . $r->article_request : '$ 0';
        $cash_abonos = !empty($r->cash_abonos) ? '$ ' . $r->cash_abonos : '$ 0';
        $cant_abonos = !empty($r->cant_abonos) ? $r->cant_abonos : '0';
        $cash_total = !empty($r->cash_total) ? '$ ' . $r->cash_total : '$ 0';

        $reportxls[] = [
          $r->user_req,
          $r->userDetail_req,
          $r->user_dismissal,
          $r->userDetail_low,
          $r->reason,
          $date_reg,
          $cash_request,
          $days_cash_request,
          $article_request,
          $cash_abonos,
          $cant_abonos,
          $cash_total];
      }
    }
  }

  public static function getArrayLowProcess($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {
        $date_reg = !empty($r->date_reg) ? date("d-m-Y H:i:s", strtotime($r->date_reg)) : 'S/N';
        $date_step1 = !empty($r->date_step1) ? date("d-m-Y H:i:s", strtotime($r->date_step1)) : 'S/N';
        $cash_total = !empty($r->cash_total) ? '$ ' . $r->cash_total : '$ 0';
        $residue_amount = !empty($r->residue_amount) ? '$ ' . $r->residue_amount : '$ 0';
        $cash_discount_total = !empty($r->cash_discount_total) ? '$ ' . $r->cash_discount_total : '$ 0';

        $reportxls[] = [
          $r->user_dismissal,
          $r->userDetail_low,
          ($r->distributor != null ? $r->distributor : 'N/A'),
          $date_reg,
          $cash_total,
          $residue_amount,
          $cash_discount_total,
          $date_step1];
      }
    }
  }

  public static function getArrayLowReport($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {
        $date_reg = !empty($r->date_reg) ? date("d-m-Y H:i:s", strtotime($r->date_reg)) : 'S/N';
        $date_step1 = !empty($r->date_step1) ? date("d-m-Y H:i:s", strtotime($r->date_step1)) : 'S/N';
        $date_step2 = !empty($r->date_step2) ? date("d-m-Y H:i:s", strtotime($r->date_step2)) : 'S/N';
        $cash_total = !empty($r->cash_total) ? '$ ' . $r->cash_total : '$ 0';
        $residue_amount = !empty($r->residue_amount) ? '$ ' . $r->residue_amount : '$ 0';
        $cash_discount_total = !empty($r->cash_discount_total) ? '$ ' . $r->cash_discount_total : '$ 0';
        $article_request = !empty($r->article_request) ? '$ ' . $r->article_request : '$ 0';
        $cash_request = !empty($r->cash_request) ? '$ ' . $r->cash_request : '$ 0';
        $cash_abonos = !empty($r->cash_abonos) ? '$ ' . $r->cash_abonos : '$ 0';
        $reason_deny = !empty($r->reason_deny) ? $r->reason_deny : 'S/N';

        //$discounted_amount = !empty($r->discounted_amount) ? '$ ' . $r->discounted_amount : ($r->discounted_amount == '0') ? '$ 0' : 'N/A';
        if (!empty($r->discounted_amount)) {
          $discounted_amount = '$ ' . $r->discounted_amount;
        } else if (($r->discounted_amount == '0')) {
          $discounted_amount = '$ 0';
        } else {
          $discounted_amount = 'N/A';
        }

        //$mount_liquidacion = !empty($r->mount_liquidacion) ? '$ ' . $r->mount_liquidacion : ($r->mount_liquidacion == '0') ? '$ 0' : 'N/A';
        if (!empty($r->mount_liquidacion)) {
          $mount_liquidacion = '$ ' . $r->mount_liquidacion;
        } else if ($r->mount_liquidacion == '0') {
          $mount_liquidacion = '$ 0';
        } else {
          $mount_liquidacion = 'N/A';
        }

        $date_liquidacion = !empty($r->date_liquidacion) ? $r->date_liquidacion : 'S/N';

        switch ($r->status) {
          case 'R':$status = 'Solicitada';
            break;
          case 'P':$status = 'En proceso';
            break;
          case 'F':$status = 'Finalizada';
            break;
          case 'D':$status = 'Rechazada';
            break;
          default:$status = 'Status Desconocido';
            break;
        }
        $reportxls[] = [
          $r->user_dismissal,
          $r->userDetail_low,
          ($r->distributor != null ? $r->distributor : 'N/A'),
          $date_reg,
          $article_request,
          $cash_request,
          $cash_abonos,
          $cash_total,
          $residue_amount,
          $cash_discount_total,
          $date_step1,
          $date_step2,
          $status,
          $reason_deny,
          $discounted_amount,
          $mount_liquidacion,
          $date_liquidacion];
      }
    }
  }

  public static function getArrayKPIDismissal($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {
        $reportxls[] = [
          $r->periodo,
          $r->regional_email,
          $r->coordinator_email,
          $r->old_articles != 0 ? $r->old_articles : "0",
          $r->decrease_articles != 0 ? $r->decrease_articles : "0",
          $r->assigned_articles != 0 ? $r->assigned_articles : "0",
          $r->kpi_result != 0 ? $r->kpi_result : "0",
          $r->lost_articles_cost != 0 ? $r->lost_articles_cost : "0",
          $r->total_perc_discount != 0 ? $r->total_perc_discount : "0",
          $r->regional_perc_discount != 0 ? $r->regional_perc_discount : "0",
          $r->coordinator_perc_discount != 0 ? $r->coordinator_perc_discount : "0",
          $r->total_amount_discount != 0 ? $r->total_amount_discount : "0",
          $r->regional_amount_discount != 0 ? $r->regional_amount_discount : "0",
          $r->coordinator_amount_discount != 0 ? $r->coordinator_amount_discount : "0"];
      }
    }
  }

  public static function getArrayInvRecicler($collection = false, &$reportxls = [])
  {
    if ($collection) {

      foreach ($collection as $r) {

        if ($r->checkOffert == 'Y' && ($r->status == 'C' || $r->status == 'M')) {
          $status = "Solicicitado";
        } elseif ($r->status == 'F' || $r->status == 'P') {
          if ($r->ReciclerType == 'C') {
            if ($r->loadInventary == 'Y') {
              $status = "OK";
            } else {
              $status = "En espera de cron inventario";
            }
          } else {
            $status = "OK";
          }
        } elseif ($r->status == 'E') {
          $status = "Error";
        } elseif ($r->checkAltan == 'Y') {
          $status = "Inconveniente con Altan";
        } elseif ($r->checkOffert == 'N' && ($r->status == 'M' || $r->status == 'C') && $r->checkAltan == 'N') {
          $status = "En espera de cron reciclaje";
        } elseif ($r->status == 'R') {
          $status = "Rechazado";
        } else {
          $status = "Desconocido";
        }

        switch ($r->origin_netwey) {
          case "one":$origin_netwey = 'Carga manual';
            break;
          case "file":$origin_netwey = 'Archivo masivo';
            break;
          case "seller":$origin_netwey = 'Peticion del seller';
            break;
          case "call_center":$origin_netwey = 'Peticion de Call Center';
            break;
          case "sftp":$origin_netwey = 'Sftp prova';
            break;

          default:$origin_netwey = 'Desconocido';
            break;
        }
        if (!empty($r->user_netwey)) {
          $user_netwey = $r->user_netwey;
        } else {
          $user_netwey = 'N/A';
        }

        if (!empty($r->codeOffert)) {
          $codeOffert = $r->codeOffert;
        } else {
          $codeOffert = 'N/A';
        }

        if (!empty($r->obs)) {
          $obs = $r->obs;
        } else {
          $obs = 'N/A';
        }

        if (!empty($r->detail_error)) {
          $detail_error = $r->detail_error;
        } else {
          $detail_error = 'N/A';
        }

        switch ($r->statusClient) {
          case "A":$statusClient = 'Activo';
            break;
          case "I":$statusClient = 'Inactivo';
            break;
          case "S":$statusClient = 'Suspendido';
            break;
          case "T":$statusClient = 'Eliminado';
            break;

          default:$statusClient = 'S/N';
            break;
        }
        if (!empty($r->dias_recharge)) {
          $dias_recharge = $r->dias_recharge;
        } else {
          $dias_recharge = 'N/A';
        }

        $reportxls[] = [
          $status,
          $r->msisdn,
          $origin_netwey,
          $user_netwey,
          $r->date_reg,
          $codeOffert,
          $obs,
          $detail_error,
          $statusClient,
          $dias_recharge];
      }

    }
  }

  public static function getArrayPaguitos($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {
        $coord = !empty($r->nameCoordFull) ? $r->nameCoordFull : 'N/A';
        $seller = !empty($r->nameSellerFull) ? $r->nameSellerFull : 'N/A';
        $client = !empty($r->nameClientFull) ? $r->nameClientFull : 'N/A';
        $amount = '$' . round($r->initial_amount, 2);
        $amountF = '$' . round($r->total_amount, 2);
        $initAmount = '$' . round(($r->total_amount - $r->initial_amount), 2);
        $dateF = date('d-m-Y H:i:s', strtotime($r->date_reg));
        $dateA = !empty($r->date_process) ? date('d-m-Y H:i:s', strtotime($r->date_process)) : 'N/A';
        $status = $r->status == 'A' ? 'Notificado' : 'Asociado';

        $reportxls[] = [
          $r->msisdn,
          $coord,
          $seller,
          $client,
          $initAmount,
          $amount,
          $amountF,
          $dateF,
          $dateA,
          $status];
      }
    }
  }

  public static function getArrayBillingsMasive($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {

        //format to date expired
        $date_expired = date_create($r->date_expired);
        $date_expired = date_format($date_expired, 'd/m/Y');

        //format to term pay
        if ($r->term == '30') {
          $term = "30 dias";
        } else if ($r->term == 'C') {
          $term = "Contado";
        }

        //format to oxxo folio date
        $oxxo_folio_date = date_create($r->oxxo_folio_date);
        $oxxo_folio_date = date_format($oxxo_folio_date, 'd/m/Y');

        //format status pay
        if ($r->status_pay == 'Y') {
          $status_pay = "Pago Completo";
        } else if ($r->status_pay == 'N') {
          $status_pay = "No Pagado";
        }

        //format date pay
        $date_pay = date_create($r->date_pay);
        $date_pay = date_format($date_pay, 'd/m/Y');

        $reportxls[] = [
          $r->place,
          $date_expired,
          $term,
          $oxxo_folio_date,
          $r->oxxo_folio_id,
          $r->oxxo_folio_nro,
          $date_pay,
          $r->doc_pay,
          $status_pay,
          $r->sub_total,
          $r->tax,
          $r->total,
          $r->pay_type,
          $r->mk_serie,
          $r->mk_folio];

      }
    }
  }

  public static function getArrayHistoryStatusInv($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r) {

        switch ($r->artic_type) {
          case 'H':$artic_type = "Internet Hogar";
            break;
          case 'T':$artic_type = "Telefonia";
            break;
          case 'M':$artic_type = "Mifi";
            break;
          case 'F':$artic_type = "Fibra";
            break;
          default:$artic_type = "N/A";
            break;
        }

        switch ($r->color) {
          case 'red':$color = "Rojo";
            break;
          case 'orange':$color = "Naranja";
            break;
          default:$color = "N/A";
            break;
        }

        $date_color = !empty($r->date_color) ? date("d-m-Y H:i:s", strtotime($r->date_color)) : 'N/A';
        $last_date_orange = !empty($r->last_date_orange) ? date("d-m-Y H:i:s", strtotime($r->last_date_orange)) : 'N/A';

        switch ($r->status) {
          case 'R':$status = 'Solicitada';
            break;
          case 'P':$status = 'En proceso';
            break;
          case 'F':$status = 'Finalizada';
            break;
          case 'D':$status = 'Rechazada';
            break;
          default:$status = 'Status Desconocido';
            break;
        }
        $reportxls[] = [
          $r->assigned,
          $r->coordination,
          $r->nameCoordinator,
          $r->region,
          $r->nameRegional,
          $r->msisdn,
          $r->title,
          $artic_type,
          $color,
          $date_color,
          $last_date_orange,
          $r->cant_orange];
      }
    }
  }

  public static function getArrayFiberInstallationsByStatus($collection = false, &$reportxls = [])
  {
    if ($collection) {
      foreach ($collection as $r)
      {

        switch ($r->status)
        {
          case 'A':$status = 'En proceso';
            break;
          case 'T':$status = 'Eliminado';
            break;
          case 'P':$status = 'Instalado';
            break;
        }

        if (!empty($r->date_activation)) {
          $date_activation = $r->date_activation;
        } else {
          $date_activation = 'N/A';
        }

        if (!empty($r->msisdn)) {
          $msisdn = $r->msisdn;
        } else {
          $msisdn = 'N/A';
        }

        if (!empty($r->mac)) {
          $mac = $r->mac;
        } else {
          $mac = 'N/A';
        }

        if (!empty($r->num_rescheduling)) {
          $num_rescheduling = $r->num_rescheduling;
        } else {
          $num_rescheduling = '0';
        }

        if (!empty($r->zone_name)) {
          $zone_name = $r->zone_name;
        } else {
          $zone_name = 'N/A';
        }

        if (!empty($r->date_instalation)) {
          $date_instalation = $r->date_instalation;
        } else {
          $date_instalation = 'N/A';
        }

        if (!empty($r->antiquity)) {
          $antiquity = $r->antiquity;
        } else {
          $antiquity = 'N/A';
        }

        $reportxls[] = [
          $msisdn,
          $mac,
          $r->client,
          $r->seller,
          $r->colony,
          $zone_name,
          $status,
          $num_rescheduling,
          $date_instalation,
          $date_activation, 
          $antiquity
        ];
      }
    }
  }

}
