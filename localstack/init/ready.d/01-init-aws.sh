#!/bin/bash
# Provisiona recursos AWS locais assim que o LocalStack fica ready.
set -euo pipefail

REGION="${AWS_DEFAULT_REGION:-us-east-1}"
ACCOUNT="000000000000"
DLQ_NAME="shipsync-jobs-dlq"
QUEUE_NAME="shipsync-jobs"
BUCKET="shipsync-local"
TABLE="shipsync-records"

echo "[init] criando DLQ ${DLQ_NAME}"
awslocal sqs create-queue --queue-name "${DLQ_NAME}" --region "${REGION}"

DLQ_ARN="arn:aws:sqs:${REGION}:${ACCOUNT}:${DLQ_NAME}"

echo "[init] criando fila ${QUEUE_NAME} com redrive para a DLQ"
awslocal sqs create-queue \
  --queue-name "${QUEUE_NAME}" \
  --region "${REGION}" \
  --attributes "{\"RedrivePolicy\":\"{\\\"deadLetterTargetArn\\\":\\\"${DLQ_ARN}\\\",\\\"maxReceiveCount\\\":\\\"3\\\"}\"}"

echo "[init] criando bucket s3://${BUCKET}"
awslocal s3 mb "s3://${BUCKET}" --region "${REGION}" || true

echo "[init] criando tabela DynamoDB ${TABLE}"
awslocal dynamodb create-table \
  --table-name "${TABLE}" \
  --attribute-definitions AttributeName=pk,AttributeType=S AttributeName=sk,AttributeType=S \
  --key-schema AttributeName=pk,KeyType=HASH AttributeName=sk,KeyType=RANGE \
  --billing-mode PAY_PER_REQUEST \
  --region "${REGION}" || true

echo "[init] recursos AWS locais prontos"
